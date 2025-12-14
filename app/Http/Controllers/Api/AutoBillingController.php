<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAutoBillingWhatsAppJob;
use App\Models\AutoBilling;
use App\Models\BillingSendLog;
use App\Services\BillingService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoBillingController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private WhatsAppService $whatsAppService
    ) {}

    /**
     * List auto billings with filters
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'is_paid' => 'nullable|boolean',
            'search' => 'nullable|string',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');
        $isPaid = $request->input('is_paid');
        $search = $request->input('search');

        $billings = $this->billingService->getAutoBillings($year, $month, $isPaid);

        // Filter by student name if search provided
        if ($search) {
            $billings = $billings->filter(function ($billing) use ($search) {
                return stripos($billing->student->name, $search) !== false;
            })->values();
        }

        return response()->json([
            'data' => $billings,
            'message' => 'Auto billings retrieved successfully',
        ]);
    }

    /**
     * Get billing details
     */
    public function show(string $id): JsonResponse
    {
        $billing = AutoBilling::with('student')->findOrFail($id);

        return response()->json([
            'data' => $billing,
            'message' => 'Billing retrieved successfully',
        ]);
    }

    /**
     * Get totals for unpaid and paid billings
     */
    public function totals(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $totals = $this->billingService->getAutoBillingsTotals(
            $request->input('year'),
            $request->input('month')
        );

        return response()->json([
            'data' => $totals,
            'message' => 'Totals retrieved successfully',
        ]);
    }

    /**
     * Mark billing as paid manually
     */
    public function markAsPaid(Request $request, string $id): JsonResponse
    {
        try {
            $this->billingService->markAsPaid($id, 'auto', 'manual');

            return response()->json([
                'message' => 'Billing marked as paid successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking billing as paid: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark billing as paid'], 500);
        }
    }

    /**
     * Send payment link via WhatsApp
     */
    public function sendWhatsApp(Request $request, string $id): JsonResponse
    {
        try {
            $billing = AutoBilling::with('student')->findOrFail($id);

            if (!$billing->student->whatsapp_number) {
                return response()->json(['error' => 'Student does not have WhatsApp number'], 400);
            }

            // Generate payment link if not exists
            if (!$billing->payment_token) {
                $billing->generatePaymentToken();
            }

            $paymentLink = url("/pay/{$billing->payment_token}");
            $monthName = date('F', mktime(0, 0, 0, $billing->month, 1));
            $currencySymbol = $billing->currency instanceof \App\Enums\Currency 
                ? $billing->currency->symbol() 
                : $billing->currency;

            $message = "Your billing for {$monthName} {$billing->year}: {$billing->total_amount} {$currencySymbol}\n";
            $message .= "Total Hours: {$billing->total_hours}\n";
            $message .= "Payment link: {$paymentLink}";

            $result = $this->whatsAppService->sendMessage(
                $billing->student->whatsapp_number,
                $message
            );

            if ($result['success']) {
                return response()->json([
                    'message' => 'WhatsApp message sent successfully',
                ]);
            }

            return response()->json(['error' => $result['message']], 500);
        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send WhatsApp message'], 500);
        }
    }

    /**
     * Generate auto billings for a specific month/year
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $generated = $this->billingService->generateAutoBillings(
                $request->input('year'),
                $request->input('month')
            );

            return response()->json([
                'data' => $generated,
                'message' => 'Auto billings generated successfully',
                'count' => count($generated),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error generating auto billings: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate auto billings'], 500);
        }
    }

    /**
     * Send WhatsApp messages to all unpaid billings for a month (queued in background)
     */
    public function sendAllWhatsApp(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $year = $request->input('year');
            $month = $request->input('month');
            $batchId = Str::uuid()->toString();

            // Get all unpaid billings for the month
            $billings = AutoBilling::with('student')
                ->where('year', $year)
                ->where('month', $month)
                ->where('is_paid', false)
                ->whereHas('student', function ($query) {
                    $query->whereNotNull('whatsapp_number')
                          ->where('whatsapp_number', '!=', '');
                })
                ->get();

            if ($billings->isEmpty()) {
                return response()->json([
                    'message' => 'No unpaid billings with WhatsApp numbers found',
                    'batch_id' => $batchId,
                    'total' => 0,
                    'sent' => 0,
                    'failed' => 0,
                ]);
            }

            // Dispatch jobs with delays to avoid rate limiting
            // Each job will be delayed by 2 seconds from the previous one
            $delay = 0;
            foreach ($billings as $billing) {
                // Skip if student is not loaded or doesn't exist
                if (!$billing->student || !$billing->student->whatsapp_number) {
                    Log::warning('Skipping billing ' . $billing->id . ' - student missing or no WhatsApp number');
                    continue;
                }

                SendAutoBillingWhatsAppJob::dispatch(
                    $billing->id,
                    $year,
                    $month,
                    $batchId
                )->delay(now()->addSeconds($delay));
                
                $delay += 2; // 2 seconds between each job to avoid WhatsApp rate limiting
            }

            Log::info("Queued {$billings->count()} WhatsApp sending jobs for batch {$batchId}");

            return response()->json([
                'message' => 'WhatsApp sending jobs queued successfully. Messages will be sent in the background.',
                'batch_id' => $batchId,
                'total' => $billings->count(),
                'status' => 'queued',
                'note' => 'Check send logs to see progress',
            ]);
        } catch (\Exception $e) {
            Log::error('Error queueing WhatsApp messages: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'error' => 'Failed to queue WhatsApp messages',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sending logs for a month
     */
    public function getSendLogs(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $year = $request->input('year');
            $month = $request->input('month');

            // Get all logs grouped by batch_id
            $logs = BillingSendLog::where('year', $year)
                ->where('month', $month)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('batch_id');

            $batches = [];
            foreach ($logs as $batchId => $batchLogs) {
                $total = $batchLogs->count();
                $sent = $batchLogs->where('status', 'success')->count();
                $failed = $batchLogs->where('status', 'failed')->count();
                $pending = $batchLogs->where('status', 'pending')->count();

                $batches[] = [
                    'batch_id' => $batchId,
                    'created_at' => $batchLogs->first()->created_at->toIso8601String(),
                    'total' => $total,
                    'sent' => $sent,
                    'failed' => $failed,
                    'pending' => $pending,
                    'logs' => $batchLogs->map(function ($log) {
                        return [
                            'id' => $log->id,
                            'student_name' => $log->student_name,
                            'phone_number' => $log->phone_number,
                            'status' => $log->status,
                            'error_message' => $log->error_message,
                            'sent_at' => $log->sent_at?->toIso8601String(),
                            'attempt_number' => $log->attempt_number,
                        ];
                    })->values(),
                ];
            }

            // Calculate totals
            $allLogs = BillingSendLog::where('year', $year)
                ->where('month', $month)
                ->get();

            return response()->json([
                'data' => [
                    'batches' => $batches,
                    'summary' => [
                        'total_students' => $allLogs->count(),
                        'total_sent' => $allLogs->where('status', 'success')->count(),
                        'total_failed' => $allLogs->where('status', 'failed')->count(),
                        'total_pending' => $allLogs->where('status', 'pending')->count(),
                    ],
                ],
                'message' => 'Sending logs retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting send logs: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve sending logs'], 500);
        }
    }

    /**
     * Resume sending WhatsApp for failed students
     */
    public function resumeSendWhatsApp(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $year = $request->input('year');
            $month = $request->input('month');
            $batchId = Str::uuid()->toString();

            // Get all failed logs for the month
            $failedLogs = BillingSendLog::where('year', $year)
                ->where('month', $month)
                ->where('status', 'failed')
                ->with('billing.student')
                ->get();

            if ($failedLogs->isEmpty()) {
                return response()->json([
                    'message' => 'No failed sending logs found',
                    'batch_id' => $batchId,
                    'total' => 0,
                    'sent' => 0,
                    'failed' => 0,
                ]);
            }

            $sent = 0;
            $failed = 0;
            $delaySeconds = 2;

            foreach ($failedLogs as $oldLog) {
                $billing = $oldLog->billing;
                if (!$billing || !$billing->student) {
                    continue;
                }

                // Create new log entry for retry
                $log = BillingSendLog::create([
                    'year' => $year,
                    'month' => $month,
                    'batch_id' => $batchId,
                    'billing_id' => $billing->id,
                    'student_id' => $billing->student_id,
                    'student_name' => $billing->student->name,
                    'phone_number' => $billing->student->whatsapp_number,
                    'status' => 'pending',
                    'attempt_number' => $oldLog->attempt_number + 1,
                ]);

                try {
                    // Generate payment token if not exists
                    if (!$billing->payment_token) {
                        $billing->generatePaymentToken();
                    }

                    $paymentUrl = url("/pay/{$billing->payment_token}");
                    $monthName = date('F', mktime(0, 0, 0, $month, 1));
                    $currencySymbol = $billing->currency instanceof \App\Enums\Currency 
                        ? $billing->currency->symbol() 
                        : $billing->currency;

                    // Build message in same format as url_launcher
                    $message = "🎓 *Almajd Academy*\n"
                        . "━━━━━━━━━━━━━━━━━━\n\n"
                        . "📋 *Invoice Details*\n"
                        . "Period: {$monthName} {$year}\n"
                        . "Hours: " . number_format($billing->total_hours, 2) . " hours\n\n"
                        . "💰 *Total Amount*\n"
                        . "*{$currencySymbol}" . number_format($billing->total_amount, 2) . "*\n"
                        . "\n\n💳 *Pay Securely:*\n{$paymentUrl}\n\n"
                        . "Thank you for choosing Almajd Academy! 🌟";

                    // Send message
                    $result = $this->whatsAppService->sendMessage(
                        $billing->student->whatsapp_number,
                        $message
                    );

                    if ($result['success']) {
                        $log->update([
                            'status' => 'success',
                            'sent_at' => now(),
                        ]);
                        $sent++;
                    } else {
                        $log->update([
                            'status' => 'failed',
                            'error_message' => $result['message'] ?? 'Unknown error',
                        ]);
                        $failed++;
                    }
                } catch (\Exception $e) {
                    $log->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                    $failed++;
                    Log::error('Error resending WhatsApp to student ' . $billing->student_id . ': ' . $e->getMessage());
                }

                // Add delay between messages
                if ($failedLogs->last() !== $oldLog) {
                    sleep($delaySeconds);
                }
            }

            return response()->json([
                'message' => 'Resume sending process completed',
                'batch_id' => $batchId,
                'total' => $failedLogs->count(),
                'sent' => $sent,
                'failed' => $failed,
            ]);
        } catch (\Exception $e) {
            Log::error('Error resuming WhatsApp sending: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to resume sending'], 500);
        }
    }
}
