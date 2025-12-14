<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AutoBilling;
use App\Models\ManualBilling;
use App\Models\PaymentLog;
use App\Models\PaymentSettings;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Show payment page (public, no auth required)
     */
    public function show(string $token)
    {
        // Find billing by token
        $autoBilling = AutoBilling::where('payment_token', $token)->first();
        $manualBilling = ManualBilling::where('payment_token', $token)->first();

        if (!$autoBilling && !$manualBilling) {
            abort(404, 'Payment link not found');
        }

        $billing = $autoBilling ?? $manualBilling;
        $billingType = $autoBilling ? 'auto' : 'manual';

        // Check if already paid
        if ($billing->is_paid) {
            return redirect()->route('payment.success', ['token' => $token]);
        }

        // Get payment settings
        $paypalEnabled = PaymentSettings::getSetting('paypal_enabled', '1');
        $anubpayEnabled = PaymentSettings::getSetting('anubpay_enabled', '0');

        // Get student/user info
        if ($billingType === 'auto') {
            $user = $autoBilling->student;
            $amount = $autoBilling->total_amount;
            $month = $autoBilling->month;
            $year = $autoBilling->year;
            $billingId = $autoBilling->id;
        } else {
            // Get first student from manual billing
            $studentIds = $manualBilling->student_ids ?? [];
            $user = !empty($studentIds) ? User::find($studentIds[0]) : null;
            $amount = $manualBilling->amount;
            $month = 'custom';
            $year = null;
            $billingId = $manualBilling->id;
        }
        
        if (!$user) {
            abort(404, 'User not found');
        }

        return view('payments.show', compact(
            'billing',
            'billingType',
            'user',
            'amount',
            'month',
            'year',
            'billingId',
            'paypalEnabled',
            'anubpayEnabled',
            'token'
        ));
    }

    /**
     * Handle PayPal success callback
     */
    public function paypalSuccess(Request $request, string $token)
    {
        $autoBilling = AutoBilling::where('payment_token', $token)->first();
        $manualBilling = ManualBilling::where('payment_token', $token)->first();

        if (!$autoBilling && !$manualBilling) {
            abort(404);
        }

        $billing = $autoBilling ?? $manualBilling;
        $billingType = $autoBilling ? 'auto' : 'manual';

        // Process PayPal payment
        $this->paymentService->processPayPalCallback(
            $request->all(),
            $billing->id,
            $billingType
        );

        return redirect()->route('payment.success', ['token' => $token]);
    }

    /**
     * Show XPay form
     */
    public function xpayForm(string $token, Request $request)
    {
        $autoBilling = AutoBilling::where('payment_token', $token)->first();
        $manualBilling = ManualBilling::where('payment_token', $token)->first();

        if (!$autoBilling && !$manualBilling) {
            abort(404);
        }

        // Get billing type from query parameter or determine from billing
        $billingType = $request->query('billing_type', $autoBilling ? 'auto' : 'manual');

        return view('payments.xpay-form', compact('token', 'billingType'));
    }

    /**
     * Process XPay payment
     */
    public function xpayProcess(Request $request, string $token)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        $autoBilling = AutoBilling::where('payment_token', $token)->first();
        $manualBilling = ManualBilling::where('payment_token', $token)->first();

        if (!$autoBilling && !$manualBilling) {
            abort(404);
        }

        $billing = $autoBilling ?? $manualBilling;
        $billingType = $request->input('billing_type', $autoBilling ? 'auto' : 'manual');
        
        if ($billingType === 'auto') {
            $user = $autoBilling->student;
        } else {
            // Get first student from manual billing
            $studentIds = $manualBilling->student_ids ?? [];
            $user = !empty($studentIds) ? User::find($studentIds[0]) : null;
        }

        if (!$user) {
            abort(404);
        }

        $amount = $billingType === 'auto' ? $autoBilling->total_amount : $manualBilling->amount;
        $currency = $billing->currency->value ?? $billing->currency;
        $month = $billingType === 'auto' ? $autoBilling->month : 'custom';

        // Use the billing's original currency and amount without conversion
        $xpayAmount = $amount;
        $xpayCurrency = $currency;

        $client = new \GuzzleHttp\Client();
        $url = config('payments.xpay.api_url');

        $data = [
            "billing_data" => [
                "name" => $request->input('name'),
                "email" => $request->input('email'),
                "phone_number" => $user->whatsapp_number ?? $request->input('phone'),
            ],
            "custom_fields" => [
                [
                    "field_label" => "user_id",
                    "field_value" => $user->id
                ],
                [
                    "field_label" => "month",
                    "field_value" => $billingType === 'auto' ? $month : 15
                ],
            ],
            "amount" => $xpayAmount,
            "currency" => $xpayCurrency,
            "variable_amount_id" => config('payments.xpay.variable_amount_id'),
            "community_id" => config('payments.xpay.community_id'),
            "pay_using" => "card"
        ];

        if ($billingType === 'manual') {
            $data["custom_fields"][] = [
                "field_label" => "billing_id",
                "field_value" => $manualBilling->id
            ];
        }

        $headers = [
            'x-api-key' => config('payments.xpay.api_key'),
            'Content-Type' => 'application/json',
        ];

        try {
            $response = $client->request('POST', $url, [
                'headers' => $headers,
                'body' => json_encode($data),
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode == 200 && isset($body['data']['iframe_url'])) {
                return redirect($body['data']['iframe_url']);
            }

            return redirect()->route('payment.cancel');
        } catch (\Exception $e) {
            Log::error('XPay process error: ' . $e->getMessage());
            return redirect()->route('payment.cancel');
        }
    }

    /**
     * Handle XPay callback
     */
    public function xpayCallback(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $userId = $request->input('user_id');
        $month = $request->input('month');
        $billingId = $request->input('billing_id');

        try {
            if ($billingId) {
                // Manual billing
                $billing = ManualBilling::find($billingId);
                $billingType = 'manual';
            } else {
                // Auto billing
                $billing = AutoBilling::where('student_id', $userId)
                    ->where('month', $month)
                    ->first();
                $billingType = 'auto';
            }

            if (!$billing) {
                return view('payments.cancel');
            }

            // Process XPay payment
            $success = $this->paymentService->processXPayCallback(
                $request->all(),
                $billing->id,
                $billingType
            );

            if ($success) {
                return view('payments.success');
            }

            return view('payments.cancel');
        } catch (\Exception $e) {
            Log::error('XPay callback error: ' . $e->getMessage());
            return view('payments.cancel');
        }
    }

    /**
     * Create AnubPay payment
     */
    public function createAnubPayPayment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'month' => 'required|string',
            'billing_id' => 'nullable|integer',
            'billing_type' => 'required|string|in:auto,manual',
        ]);

        try {
            $result = $this->paymentService->createAnubPayPayment(
                $request->input('user_id'),
                $request->input('amount'),
                $request->input('currency'),
                $request->input('month'),
                $request->input('billing_id'),
                $request->input('billing_type')
            );

            if ($result['success']) {
                // Store payment info in session
                Session::put('anubpay_payment', [
                    'user_id' => $request->input('user_id'),
                    'amount' => $request->input('amount'),
                    'month' => $request->input('month'),
                    'billing_id' => $request->input('billing_id'),
                    'billing_type' => $request->input('billing_type'),
                    'pid' => $result['pid'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'redirect_url' => $result['redirect_url'],
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Payment creation failed',
            ], 400);
        } catch (\Exception $e) {
            Log::error('AnubPay creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Payment creation failed',
            ], 500);
        }
    }

    /**
     * Handle AnubPay webhook callback
     */
    public function handleAnubPayPayment(Request $request)
    {
        try {
            $data = $request->all();
            
            Log::info('AnubPay webhook received', ['data' => $data]);

            // Extract billing info
            $additionalData = $data['additional_data'] ?? null;
            if (is_string($additionalData)) {
                $additionalData = json_decode($additionalData, true);
            }

            $billingId = $additionalData['billing_id'] ?? $data['billing_id'] ?? null;
            $billingType = $additionalData['billing_type'] ?? $data['billing_type'] ?? 'auto';

            if (!$billingId) {
                Log::warning('AnubPay webhook: No billing_id found');
                return response()->json(['error' => 'No billing_id found'], 400);
            }

            // Process payment
            $success = $this->paymentService->processAnubPayCallback(
                $data,
                $billingId,
                $billingType
            );

            if ($success) {
                return response()->json(['success' => true]);
            }

            return response()->json(['error' => 'Payment processing failed'], 400);
        } catch (\Exception $e) {
            Log::error('AnubPay webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle AnubPay success redirect
     */
    public function anubPaySuccess(Request $request, string $month)
    {
        $paymentData = Session::get('anubpay_payment');

        if (!$paymentData) {
            return redirect()->route('payment.cancel');
        }

        // Mark billing as paid
        if ($paymentData['billing_type'] === 'manual') {
            $billing = ManualBilling::find($paymentData['billing_id']);
            if ($billing) {
                $billing->update([
                    'is_paid' => true,
                    'paid_at' => now(),
                    'payment_method' => 'anubpay',
                ]);
            }
            Session::forget('anubpay_payment');
            return redirect()->route('payment.success', ['token' => $billing->payment_token ?? '']);
        } else {
            $billing = AutoBilling::where('student_id', $paymentData['user_id'])
                ->where('month', $paymentData['month'])
                ->first();
            
            if ($billing) {
                $billing->update([
                    'is_paid' => true,
                    'paid_at' => now(),
                    'payment_method' => 'anubpay',
                ]);
            }
            Session::forget('anubpay_payment');
            return redirect()->route('payment.success', ['token' => $billing->payment_token ?? '']);
        }
    }

    /**
     * Show success page
     */
    public function success(string $token)
    {
        $autoBilling = AutoBilling::where('payment_token', $token)->first();
        $manualBilling = ManualBilling::where('payment_token', $token)->first();

        if (!$autoBilling && !$manualBilling) {
            abort(404);
        }

        $billing = $autoBilling ?? $manualBilling;

        return view('payments.success', compact('billing'));
    }

    /**
     * Show cancel page
     */
    public function cancel()
    {
        return view('payments.cancel');
    }

    /**
     * Check payment status
     */
    public function checkStatus(string $token)
    {
        $autoBilling = AutoBilling::where('payment_token', $token)->first();
        $manualBilling = ManualBilling::where('payment_token', $token)->first();

        if (!$autoBilling && !$manualBilling) {
            return response()->json(['error' => 'Payment link not found'], 404);
        }

        $billing = $autoBilling ?? $manualBilling;

        return response()->json([
            'is_paid' => $billing->is_paid,
            'paid_at' => $billing->paid_at,
            'payment_method' => $billing->payment_method,
        ]);
    }
}
