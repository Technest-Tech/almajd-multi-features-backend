<?php

namespace App\Jobs;

use App\Models\AutoBilling;
use App\Models\BillingSendLog;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAutoBillingWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $billingId;
    public $year;
    public $month;
    public $batchId;
    public $tries = 3; // Maximum number of attempts
    public $timeout = 60; // Timeout in seconds

    /**
     * Create a new job instance.
     */
    public function __construct(int $billingId, int $year, int $month, string $batchId)
    {
        $this->billingId = $billingId;
        $this->year = $year;
        $this->month = $month;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        $billing = AutoBilling::with('student')->find($this->billingId);
        
        if (!$billing) {
            Log::warning("Billing not found: {$this->billingId}");
            return;
        }

        if (!$billing->student) {
            Log::warning("Student not found for billing: {$this->billingId}");
            return;
        }

        if (!$billing->student->whatsapp_number) {
            Log::warning("Student {$billing->student_id} has no WhatsApp number");
            return;
        }

        // Create or update log entry
        $log = BillingSendLog::firstOrCreate(
            [
                'billing_id' => $billing->id,
                'batch_id' => $this->batchId,
            ],
            [
                'year' => $this->year,
                'month' => $this->month,
                'student_id' => $billing->student_id,
                'student_name' => $billing->student->name ?? 'Unknown',
                'phone_number' => $billing->student->whatsapp_number,
                'status' => 'pending',
                'attempt_number' => $this->attempts(),
            ]
        );

        // Update attempt number if retrying
        if ($this->attempts() > 1) {
            $log->update(['attempt_number' => $this->attempts()]);
        }

        try {
            // Generate payment token if not exists
            if (!$billing->payment_token) {
                $billing->generatePaymentToken();
                $billing->refresh();
            }

            $paymentUrl = url("/pay/{$billing->payment_token}");
            $monthName = date('F', mktime(0, 0, 0, $this->month, 1));
            
            // Handle currency safely
            $currencySymbol = 'SAR'; // Default
            if ($billing->currency) {
                if ($billing->currency instanceof \App\Enums\Currency) {
                    $currencySymbol = $billing->currency->symbol();
                } else {
                    $currencySymbol = (string) $billing->currency;
                }
            }

            // Build message in same format as url_launcher
            $message = "🎓 *Almajd Academy*\n"
                . "━━━━━━━━━━━━━━━━━━\n\n"
                . "📋 *Invoice Details*\n"
                . "Period: {$monthName} {$this->year}\n"
                . "Hours: " . number_format((float) $billing->total_hours, 2) . " hours\n\n"
                . "💰 *Total Amount*\n"
                . "*{$currencySymbol}" . number_format((float) $billing->total_amount, 2) . "*\n"
                . "\n\n💳 *Pay Securely:*\n{$paymentUrl}\n\n"
                . "Thank you for choosing Almajd Academy! 🌟";

            // Send message
            $result = $whatsAppService->sendMessage(
                $billing->student->whatsapp_number,
                $message
            );

            if ($result['success']) {
                $log->update([
                    'status' => 'success',
                    'sent_at' => now(),
                ]);
                Log::info("WhatsApp sent successfully for billing {$this->billingId}");
            } else {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $result['message'] ?? 'Unknown error',
                ]);
                Log::error("WhatsApp send failed for billing {$this->billingId}: " . ($result['message'] ?? 'Unknown error'));
                // Throw exception to trigger retry
                throw new \Exception($result['message'] ?? 'Failed to send WhatsApp message');
            }
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error("Error sending WhatsApp in job for billing {$this->billingId}: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry if attempts remaining
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $log = BillingSendLog::where('billing_id', $this->billingId)
            ->where('batch_id', $this->batchId)
            ->first();

        if ($log) {
            $log->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }

        Log::error("Job failed permanently for billing {$this->billingId}: " . $exception->getMessage());
    }
}
