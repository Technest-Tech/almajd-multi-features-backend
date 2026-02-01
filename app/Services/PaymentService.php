<?php

namespace App\Services;

use App\Models\AutoBilling;
use App\Models\BillingPayment;
use App\Models\ManualBilling;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Process PayPal payment callback
     */
    public function processPayPalCallback(array $data, int $billingId, string $type): bool
    {
        try {
            // Check if billing already paid to avoid duplicate processing
            $billing = $type === 'auto' 
                ? AutoBilling::find($billingId)
                : ManualBilling::find($billingId);

            if (!$billing) {
                Log::warning('PayPal callback: Billing not found', [
                    'billing_id' => $billingId,
                    'type' => $type,
                ]);
                return false;
            }

            // If already paid, return true (idempotent)
            if ($billing->is_paid) {
                Log::info('PayPal callback: Billing already paid', [
                    'billing_id' => $billingId,
                    'type' => $type,
                ]);
                return true;
            }

            // Mark billing as paid
            $billingService = new BillingService();
            $billingService->markAsPaid($billingId, $type, 'paypal');

            // Log payment
            BillingPayment::create([
                'billing_id' => $billingId,
                'billing_type' => $type,
                'payment_method' => 'paypal',
                'transaction_id' => $data['transaction_id'] ?? $data['paymentId'] ?? null,
                'amount' => $billing->total_amount ?? $billing->amount,
                'currency' => $billing->currency->value ?? $billing->currency,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            Log::info('PayPal payment marked as paid', [
                'billing_id' => $billingId,
                'type' => $type,
                'transaction_id' => $data['transaction_id'] ?? $data['paymentId'] ?? null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('PayPal callback error', [
                'error' => $e->getMessage(),
                'data' => $data,
                'billing_id' => $billingId,
                'type' => $type,
            ]);
            return false;
        }
    }

    /**
     * Process XPay payment callback
     */
    public function processXPayCallback(array $data, int $billingId, string $type): bool
    {
        try {
            $transactionId = $data['transaction_id'] ?? null;
            $transactionStatus = $data['transaction_status'] ?? null;
            
            if (!$transactionId) {
                Log::warning('XPay callback: No transaction_id provided', [
                    'billing_id' => $billingId,
                    'data' => $data,
                ]);
                return false;
            }

            // Check if billing exists
            $billing = $type === 'auto' 
                ? AutoBilling::find($billingId)
                : ManualBilling::find($billingId);

            if (!$billing) {
                Log::warning('XPay callback: Billing not found', [
                    'billing_id' => $billingId,
                    'type' => $type,
                ]);
                return false;
            }

            // If already paid, return true (idempotent)
            if ($billing->is_paid) {
                Log::info('XPay callback: Billing already paid', [
                    'billing_id' => $billingId,
                    'type' => $type,
                    'transaction_id' => $transactionId,
                ]);
                return true;
            }

            // If transaction_status is SUCCESSFUL in callback, trust it and mark as paid
            // Otherwise, verify with XPay API
            if ($transactionStatus === 'SUCCESSFUL') {
                Log::info('XPay callback: Transaction status is SUCCESSFUL, marking as paid', [
                    'billing_id' => $billingId,
                    'transaction_id' => $transactionId,
                ]);
            } else {
                // Verify transaction with XPay API
                try {
                    $client = new Client();
                    $url = config('payments.xpay.transaction_url') . "/{$transactionId}";
                    
                    $response = $client->request('GET', $url, [
                        'headers' => [
                            'x-api-key' => config('payments.xpay.api_key'),
                            'Content-Type' => 'application/json',
                        ],
                        'timeout' => 10, // Add timeout
                    ]);

                    $statusCode = $response->getStatusCode();
                    $body = json_decode($response->getBody()->getContents(), true);

                    Log::info('XPay verification API response', [
                        'status_code' => $statusCode,
                        'body' => $body,
                        'transaction_id' => $transactionId,
                    ]);

                    if ($statusCode != 200 || !isset($body['status']) || $body['status'] !== 'SUCCESSFUL') {
                        Log::warning('XPay verification failed', [
                            'status_code' => $statusCode,
                            'body' => $body,
                            'transaction_id' => $transactionId,
                        ]);
                        return false;
                    }
                } catch (\Exception $e) {
                    Log::error('XPay verification API error', [
                        'error' => $e->getMessage(),
                        'transaction_id' => $transactionId,
                        'billing_id' => $billingId,
                    ]);
                    // If verification fails but callback says SUCCESSFUL, still process
                    // This handles cases where XPay API is temporarily unavailable
                    if ($transactionStatus !== 'SUCCESSFUL') {
                        return false;
                    }
                }
            }

            // Mark billing as paid
            $billingService = new BillingService();
            $billingService->markAsPaid($billingId, $type, 'xpay');

            // Log payment
            BillingPayment::create([
                'billing_id' => $billingId,
                'billing_type' => $type,
                'payment_method' => 'xpay',
                'transaction_id' => $transactionId,
                'amount' => $billing->total_amount ?? $billing->amount,
                'currency' => $billing->currency->value ?? $billing->currency,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            Log::info('XPay payment marked as paid', [
                'billing_id' => $billingId,
                'type' => $type,
                'transaction_id' => $transactionId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('XPay callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'billing_id' => $billingId,
                'type' => $type,
            ]);
            return false;
        }
    }

    /**
     * Create AnubPay payment
     */
    public function createAnubPayPayment(int $userId, float $amount, string $currency, string $month, ?int $billingId = null, string $type = 'auto', ?int $year = null): array
    {
        try {
            $client = new Client();
            $url = config('payments.anubpay.api_url');

            $data = [
                'token' => config('payments.anubpay.token'),
                'title' => "Billing for month {$month}",
                'amount' => $amount,
                'currency' => $currency,
                'billing_id' => $billingId,
                'user_id' => $userId,
                'month' => $month,
                'method' => 'card,paypal',
                'description' => "Payment for {$month} billing",
            ];
            
            // Add year for auto billings
            if ($type === 'auto' && $year) {
                $data['year'] = $year;
            }

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $data,
            ]);

            $responseData = json_decode($response->getBody(), true);

            if ($responseData['status'] === 'success') {
                // Log payment as pending
                BillingPayment::create([
                    'billing_id' => $billingId ?? 0,
                    'billing_type' => $type,
                    'payment_method' => 'anubpay',
                    'transaction_id' => $responseData['data']['pid'] ?? null,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'pending',
                ]);

                return [
                    'success' => true,
                    'redirect_url' => $responseData['data']['slug'] ?? null,
                    'pid' => $responseData['data']['pid'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'Payment creation failed',
            ];
        } catch (\Exception $e) {
            Log::error('AnubPay creation error', [
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process AnubPay webhook callback
     */
    public function processAnubPayCallback(array $data, int $billingId, string $type): bool
    {
        try {
            // Check if payment was successful
            // If status field exists and is not 1, payment failed
            // If status field doesn't exist, assume successful (webhook was sent)
            if (isset($data['status']) && $data['status'] != 1) {
                Log::warning('AnubPay payment not successful', [
                    'status' => $data['status'],
                    'billing_id' => $billingId,
                ]);
                return false;
            }

            // Extract data from additional_data if present
            $additionalData = $data['additional_data'] ?? null;
            if (is_string($additionalData)) {
                $additionalData = json_decode($additionalData, true);
            }

            $userId = $additionalData['user_id'] ?? $data['user_id'] ?? null;
            $month = $additionalData['month'] ?? $data['month'] ?? null;
            $year = $additionalData['year'] ?? $data['year'] ?? null;
            $billingIdFromData = $additionalData['billing_id'] ?? $data['billing_id'] ?? $billingId;
            
            // Convert to integers
            if ($userId) {
                $userId = (int) $userId;
            }
            if ($month) {
                $month = (int) $month;
            }
            if ($year) {
                $year = (int) $year;
            }
            if ($billingIdFromData) {
                $billingIdFromData = (int) $billingIdFromData;
            }

            // Find billing based on type
            if ($type === 'auto') {
                if ($billingIdFromData) {
                    // If billing_id is provided, use it
                    $billing = AutoBilling::find($billingIdFromData);
                } elseif ($userId && $month) {
                    // Otherwise, find by user_id, month, and year
                    $query = AutoBilling::where('student_id', $userId)
                        ->where('month', $month);
                    
                    if ($year) {
                        $query->where('year', $year);
                    }
                    
                    $billing = $query->first();
                    
                    // If not found with year, try without year (fallback for old payments)
                    if (!$billing && $userId && $month) {
                        Log::warning('AnubPay callback: Auto billing not found with year, trying without year', [
                            'user_id' => $userId,
                            'month' => $month,
                            'year' => $year,
                        ]);
                        $billing = AutoBilling::where('student_id', $userId)
                            ->where('month', $month)
                            ->orderBy('year', 'desc') // Get the most recent one
                            ->first();
                    }
                } else {
                    $billing = null;
                }
            } else {
                // Manual billing
                $billing = $billingIdFromData ? ManualBilling::find($billingIdFromData) : null;
            }

            if (!$billing) {
                Log::warning('AnubPay callback: Billing not found', [
                    'billing_id' => $billingIdFromData,
                    'type' => $type,
                    'user_id' => $userId,
                    'month' => $month,
                    'year' => $year,
                ]);
                return false;
            }
            
            // Update billingIdFromData to the found billing's ID
            $billingIdFromData = $billing->id;

            // If already paid, return true (idempotent)
            if ($billing->is_paid) {
                Log::info('AnubPay callback: Billing already paid', [
                    'billing_id' => $billingIdFromData,
                    'type' => $type,
                ]);
                return true;
            }

            // Mark billing as paid
            $billingService = new BillingService();
            $billingService->markAsPaid($billingIdFromData, $type, 'anubpay');

            // Update or create payment log
            $payment = BillingPayment::where('billing_id', $billingIdFromData)
                ->where('billing_type', $type)
                ->where('payment_method', 'anubpay')
                ->where('status', 'pending')
                ->first();

            if ($payment) {
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'transaction_id' => $data['pid'] ?? $data['transaction_id'] ?? null,
                ]);
            } else {
                // Create payment log if doesn't exist
                BillingPayment::create([
                    'billing_id' => $billingIdFromData,
                    'billing_type' => $type,
                    'payment_method' => 'anubpay',
                    'transaction_id' => $data['pid'] ?? $data['transaction_id'] ?? null,
                    'amount' => $billing->total_amount ?? $billing->amount,
                    'currency' => $billing->currency->value ?? $billing->currency,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            Log::info('AnubPay payment marked as paid', [
                'billing_id' => $billingIdFromData,
                'type' => $type,
                'transaction_id' => $data['pid'] ?? $data['transaction_id'] ?? null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('AnubPay webhook error', [
                'error' => $e->getMessage(),
                'data' => $data,
                'billing_id' => $billingId,
                'type' => $type,
            ]);
            return false;
        }
    }
}
