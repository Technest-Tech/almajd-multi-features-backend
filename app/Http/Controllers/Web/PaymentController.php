<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AutoBilling;
use App\Models\ManualBilling;
use App\Models\PaymentLog;
use App\Models\PaymentSettings;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Spatie\LaravelPdf\Facades\Pdf;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private ReportService $reportService
    ) {}

    /**
     * Show payment page (public, no auth required)
     */
    public function show(string $token)
    {
        try {
            // Find billing by token
            $autoBilling = AutoBilling::where('payment_token', $token)->first();
            $manualBilling = ManualBilling::where('payment_token', $token)->first();

            if (!$autoBilling && !$manualBilling) {
                Log::warning('Payment link not found', ['token' => $token]);
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
                if (!$user) {
                    Log::error('Auto billing student not found', [
                        'billing_id' => $autoBilling->id,
                        'student_id' => $autoBilling->student_id,
                        'token' => $token,
                    ]);
                    abort(404, 'Student not found for this billing');
                }
                $amount = $autoBilling->total_amount;
                $month = $autoBilling->month;
                $year = $autoBilling->year;
                $billingId = $autoBilling->id;
            } else {
                // Get first student from manual billing
                $studentIds = $manualBilling->student_ids ?? [];
                if (empty($studentIds) || !is_array($studentIds)) {
                    Log::error('Manual billing has no students', [
                        'billing_id' => $manualBilling->id,
                        'token' => $token,
                        'student_ids' => $studentIds,
                    ]);
                    abort(404, 'No students found for this billing');
                }
                $user = User::find($studentIds[0]);
                if (!$user) {
                    Log::error('Manual billing student not found', [
                        'billing_id' => $manualBilling->id,
                        'student_id' => $studentIds[0],
                        'token' => $token,
                    ]);
                    abort(404, 'Student not found for this billing');
                }
                $amount = $manualBilling->amount;
                $month = 'custom';
                $year = null;
                $billingId = $manualBilling->id;
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
        } catch (\Exception $e) {
            Log::error('Payment page error', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'An error occurred while loading the payment page. Please try again later.');
        }
    }

    /**
     * Handle PayPal success callback
     */
    public function paypalSuccess(Request $request, string $token)
    {
        Log::info('PayPal success callback received', [
            'token' => $token,
            'request_data' => $request->all(),
        ]);

        $autoBilling = AutoBilling::where('payment_token', $token)->first();
        $manualBilling = ManualBilling::where('payment_token', $token)->first();

        if (!$autoBilling && !$manualBilling) {
            Log::warning('PayPal success: Billing not found', ['token' => $token]);
            abort(404);
        }

        $billing = $autoBilling ?? $manualBilling;
        $billingType = $autoBilling ? 'auto' : 'manual';

        Log::info('PayPal success: Processing payment', [
            'billing_id' => $billing->id,
            'billing_type' => $billingType,
            'is_paid' => $billing->is_paid,
            'paymentId' => $request->input('paymentId'),
            'transaction_id' => $request->input('transaction_id'),
        ]);

        // Process PayPal payment
        $success = $this->paymentService->processPayPalCallback(
            $request->all(),
            $billing->id,
            $billingType
        );

        if ($success) {
            Log::info('PayPal payment processed successfully', [
                'billing_id' => $billing->id,
                'billing_type' => $billingType,
            ]);
            return redirect()->route('payment.success', ['token' => $token]);
        }

        Log::warning('PayPal payment processing failed', [
            'billing_id' => $billing->id,
            'billing_type' => $billingType,
        ]);
        return redirect()->route('payment.cancel');
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
        } else {
            // Add year for auto billings
            $data["custom_fields"][] = [
                "field_label" => "year",
                "field_value" => $autoBilling->year
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
        // Log incoming callback for debugging
        Log::info('XPay callback received', [
            'method' => $request->method(),
            'all_params' => $request->all(),
            'transaction_status' => $request->input('transaction_status'),
        ]);

        $transactionId = $request->input('transaction_id');
        $userId = $request->input('user_id');
        $month = $request->input('month');
        $year = $request->input('year');
        $billingId = $request->input('billing_id');
        $transactionStatus = $request->input('transaction_status');

        // Check if transaction status is successful
        if ($transactionStatus && $transactionStatus !== 'SUCCESSFUL') {
            Log::warning('XPay callback: Transaction not successful', [
                'transaction_status' => $transactionStatus,
                'transaction_id' => $transactionId,
            ]);
            return view('payments.cancel');
        }

        try {
            // Convert billing_id to integer if it's a string
            if ($billingId) {
                $billingId = (int) $billingId;
            }
            
            // Convert user_id, month, and year to integers
            if ($userId) {
                $userId = (int) $userId;
            }
            if ($month) {
                $month = (int) $month;
            }
            if ($year) {
                $year = (int) $year;
            }
            
            if ($billingId) {
                // Manual billing
                $billing = ManualBilling::find($billingId);
                $billingType = 'manual';
            } else {
                // Auto billing - need year, month, and student_id
                $query = AutoBilling::where('student_id', $userId)
                    ->where('month', $month);
                
                if ($year) {
                    $query->where('year', $year);
                }
                
                $billing = $query->first();
                $billingType = 'auto';
                
                // If not found with year, try without year (fallback for old payments)
                if (!$billing && $userId && $month) {
                    Log::warning('XPay callback: Auto billing not found with year, trying without year', [
                        'user_id' => $userId,
                        'month' => $month,
                        'year' => $year,
                    ]);
                    $billing = AutoBilling::where('student_id', $userId)
                        ->where('month', $month)
                        ->orderBy('year', 'desc') // Get the most recent one
                        ->first();
                }
            }

            if (!$billing) {
                Log::warning('XPay callback: Billing not found', [
                    'billing_id' => $billingId,
                    'user_id' => $userId,
                    'month' => $month,
                    'year' => $year,
                ]);
                return view('payments.cancel');
            }

            // Process XPay payment
            $success = $this->paymentService->processXPayCallback(
                $request->all(),
                $billing->id,
                $billingType
            );

            if ($success) {
                Log::info('XPay callback: Payment processed successfully', [
                    'billing_id' => $billing->id,
                    'transaction_id' => $transactionId,
                ]);
                return view('payments.success');
            }

            Log::warning('XPay callback: Payment processing failed', [
                'billing_id' => $billing->id,
                'transaction_id' => $transactionId,
            ]);
            return view('payments.cancel');
        } catch (\Exception $e) {
            Log::error('XPay callback error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);
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
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
        ]);

        try {
            $result = $this->paymentService->createAnubPayPayment(
                $request->input('user_id'),
                $request->input('amount'),
                $request->input('currency'),
                $request->input('month'),
                $request->input('billing_id'),
                $request->input('billing_type'),
                $request->input('year'),
                $request->input('customer_name'),
                $request->input('customer_email'),
                $request->input('customer_phone')
            );

            if ($result['success']) {
                // Store payment info in session
                Session::put('anubpay_payment', [
                    'user_id' => $request->input('user_id'),
                    'amount' => $request->input('amount'),
                    'month' => $request->input('month'),
                    'year' => $request->input('year'),
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
            
            Log::info('AnubPay webhook received', [
                'data' => $data,
                'keys' => array_keys($data),
            ]);

            // Check if payment was successful - like old system
            if (isset($data['status'])) {
                if ($data['status'] != 1) {
                    Log::warning('AnubPay payment not successful', ['status' => $data['status']]);
                    return response()->json(['error' => 'Payment not successful'], 400);
                }
            } else {
                // No status field - assume payment is successful since webhook was sent
                Log::info('AnubPay webhook: No status field found, assuming payment successful');
            }

            // Extract billing info from additional_data first
            $additionalData = $data['additional_data'] ?? null;
            if (is_string($additionalData)) {
                $additionalData = json_decode($additionalData, true);
            }

            // Get values from additional_data or directly from request
            $billingId = $additionalData['billing_id'] ?? $data['billing_id'] ?? null;
            $userId = $additionalData['user_id'] ?? $data['user_id'] ?? null;
            $month = $additionalData['month'] ?? $data['month'] ?? null;
            $year = $additionalData['year'] ?? $data['year'] ?? null;
            $billingType = $additionalData['billing_type'] ?? $data['billing_type'] ?? null;
            
            Log::info('AnubPay webhook: Extracted data', [
                'billing_id' => $billingId,
                'user_id' => $userId,
                'month' => $month,
                'year' => $year,
                'billing_type' => $billingType,
            ]);
            
            // Convert to integers
            if ($billingId) $billingId = (int) $billingId;
            if ($userId) $userId = (int) $userId;
            if ($month) $month = (int) $month;
            if ($year) $year = (int) $year;
            
            // CASE 1: Manual billing with billing_id
            if ($billingId && $billingType === 'manual') {
                $billing = ManualBilling::find($billingId);
                if ($billing && !$billing->is_paid) {
                    $billing->update([
                        'is_paid' => true,
                        'paid_at' => now(),
                        'payment_method' => 'anubpay',
                    ]);
                    Log::info('AnubPay webhook: Manual billing marked as paid', ['billing_id' => $billingId]);
                    return response()->json(['success' => true]);
                }
            }
            
            // CASE 2: Auto billing with billing_id
            if ($billingId && ($billingType === 'auto' || !$billingType)) {
                $billing = AutoBilling::find($billingId);
                if ($billing && !$billing->is_paid) {
                    $billing->update([
                        'is_paid' => true,
                        'paid_at' => now(),
                        'payment_method' => 'anubpay',
                    ]);
                    Log::info('AnubPay webhook: Auto billing marked as paid by ID', ['billing_id' => $billingId]);
                    return response()->json(['success' => true]);
                }
            }
            
            // CASE 3: Find auto billing by user_id, month, and year
            if ($userId && $month) {
                $query = AutoBilling::where('student_id', $userId)
                    ->where('month', $month)
                    ->where('is_paid', false);
                
                if ($year) {
                    $query->where('year', $year);
                }
                
                $billing = $query->first();
                
                // Fallback: try without year filter
                if (!$billing) {
                    $billing = AutoBilling::where('student_id', $userId)
                        ->where('month', $month)
                        ->where('is_paid', false)
                        ->orderBy('year', 'desc')
                        ->first();
                }
                
                if ($billing) {
                    $billing->update([
                        'is_paid' => true,
                        'paid_at' => now(),
                        'payment_method' => 'anubpay',
                    ]);
                    Log::info('AnubPay webhook: Auto billing marked as paid by user_id/month', [
                        'billing_id' => $billing->id,
                        'user_id' => $userId,
                        'month' => $month,
                    ]);
                    return response()->json(['success' => true]);
                }
            }

            Log::warning('AnubPay webhook: Could not find billing to mark as paid', [
                'billing_id' => $billingId,
                'user_id' => $userId,
                'month' => $month,
                'year' => $year,
                'billing_type' => $billingType,
            ]);
            
            // Return success anyway to acknowledge webhook (like old system)
            return response()->json(['success' => true, 'message' => 'Webhook received but no billing found']);
            
        } catch (\Exception $e) {
            Log::error('AnubPay webhook error: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle AnubPay success redirect
     */
    public function anubPaySuccess(Request $request, string $month)
    {
        $paymentData = Session::get('anubpay_payment');

        Log::info('AnubPay success redirect', [
            'month' => $month,
            'payment_data' => $paymentData,
        ]);

        if (!$paymentData) {
            Log::warning('AnubPay success: No session data found');
            return redirect()->route('payment.cancel');
        }

        // Mark billing as paid
        if (($paymentData['billing_type'] ?? '') === 'manual') {
            $billing = ManualBilling::find($paymentData['billing_id']);
            if ($billing && !$billing->is_paid) {
                $billing->update([
                    'is_paid' => true,
                    'paid_at' => now(),
                    'payment_method' => 'anubpay',
                ]);
                Log::info('AnubPay success: Manual billing marked as paid', ['billing_id' => $billing->id]);
            }
            Session::forget('anubpay_payment');
            return redirect()->route('payment.success', ['token' => $billing->payment_token ?? '']);
        } else {
            // Auto billing - find by user_id, month, and optionally year
            $query = AutoBilling::where('student_id', $paymentData['user_id'])
                ->where('month', $paymentData['month']);
            
            // Add year filter if available
            if (!empty($paymentData['year'])) {
                $query->where('year', $paymentData['year']);
            }
            
            $billing = $query->first();
            
            // Fallback: try without year if not found
            if (!$billing && !empty($paymentData['user_id']) && !empty($paymentData['month'])) {
                $billing = AutoBilling::where('student_id', $paymentData['user_id'])
                    ->where('month', $paymentData['month'])
                    ->orderBy('year', 'desc')
                    ->first();
            }
            
            if ($billing && !$billing->is_paid) {
                $billing->update([
                    'is_paid' => true,
                    'paid_at' => now(),
                    'payment_method' => 'anubpay',
                ]);
                Log::info('AnubPay success: Auto billing marked as paid', ['billing_id' => $billing->id]);
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

    /**
     * Download month report for student
     */
    public function downloadReport(string $token)
    {
        // Find billing by token
        $autoBilling = AutoBilling::where('payment_token', $token)->first();
        $manualBilling = ManualBilling::where('payment_token', $token)->first();

        if (!$autoBilling && !$manualBilling) {
            abort(404, 'Payment link not found');
        }

        // Only allow for auto billing (monthly reports)
        if (!$autoBilling) {
            abort(404, 'Report only available for monthly billing');
        }

        $student = $autoBilling->student;
        
        if (!$student) {
            abort(404, 'Student not found');
        }

        // Calculate date range for the month
        $year = $autoBilling->year;
        $month = $autoBilling->month;
        
        // Get first and last day of the month
        $fromDate = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $toDate = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

        try {
            $lessons = $this->reportService->getStudentLessons($student->id, $fromDate, $toDate);
            $totalCost = $this->reportService->calculateTotalCost($lessons);

            // Sanitize filename to avoid issues with special characters
            $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $student->name);
            $filename = 'student-report-' . $sanitizedName . '-' . $month . '-' . $year . '.pdf';

            $pdf = Pdf::view('reports.student_report', [
                'student' => $student,
                'lessons' => $lessons,
                'totalCost' => $totalCost,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'reportService' => $this->reportService,
            ])
            ->format('a4')
            ->name($filename)
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                    ->margins(10, 10, 10, 10, 'mm')
                    ->showBackground()
                    ->waitUntilNetworkIdle()
                    ->timeout(120);
            });

            return $pdf->download();
        } catch (\Exception $e) {
            Log::error('Payment page report generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'student_id' => $student->id,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ]);
            abort(500, 'Failed to generate report: ' . $e->getMessage());
        }
    }
}
