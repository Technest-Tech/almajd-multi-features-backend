<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutoBilling;
use App\Models\BillingPayment;
use App\Models\ManualBilling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentDashboardController extends Controller
{
    /**
     * Get payment dashboard statistics for a specific month/year
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        try {
            // Get paid payments from BillingPayment table for the selected month/year
            $paidPayments = BillingPayment::where('status', 'paid')
                ->whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->get();

            // Get paid auto billings directly (where is_paid = true and paid_at matches month/year)
            $paidAutoBillings = AutoBilling::where('year', $year)
                ->where('month', $month)
                ->where('is_paid', true)
                ->whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->get();

            // Get paid manual billings directly (where is_paid = true and paid_at matches month/year)
            $paidManualBillings = ManualBilling::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('is_paid', true)
                ->whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->get();


            // Get unpaid auto billings for the selected month/year
            $unpaidAutoBillings = AutoBilling::where('year', $year)
                ->where('month', $month)
                ->where('is_paid', false)
                ->get();

            // Get unpaid manual billings created in the selected month/year
            $unpaidManualBillings = ManualBilling::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('is_paid', false)
                ->get();

            // Calculate statistics by payment gateway
            $gatewayStats = $this->calculateGatewayStatistics($paidPayments, $paidAutoBillings, $paidManualBillings);
            
            // Calculate statistics by currency
            $currencyStats = $this->calculateCurrencyStatistics($paidPayments, $paidAutoBillings, $paidManualBillings, $unpaidAutoBillings, $unpaidManualBillings);
            
            // Calculate overall statistics
            $overallStats = $this->calculateOverallStatistics($paidPayments, $paidAutoBillings, $paidManualBillings, $unpaidAutoBillings, $unpaidManualBillings);

            return response()->json([
                'data' => [
                    'gateways' => $gatewayStats,
                    'currencies' => $currencyStats,
                    'overall' => $overallStats,
                ],
                'message' => 'Payment dashboard statistics retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve payment dashboard statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate statistics grouped by payment gateway
     */
    private function calculateGatewayStatistics($paidPayments, $paidAutoBillings, $paidManualBillings): array
    {
        $gateways = ['paypal', 'xpay', 'anubpay'];
        $gatewayStats = [];

        // Pre-load all billing IDs to avoid N+1 queries
        $autoBillingIds = $paidPayments->where('billing_type', 'auto')->pluck('billing_id')->unique();
        $manualBillingIds = $paidPayments->where('billing_type', 'manual')->pluck('billing_id')->unique();
        
        $autoBillings = AutoBilling::whereIn('id', $autoBillingIds)->get()->keyBy('id');
        $manualBillings = ManualBilling::whereIn('id', $manualBillingIds)->get()->keyBy('id');

        foreach ($gateways as $gateway) {
            $gatewayPayments = $paidPayments->where('payment_method', $gateway);
            
            // Also include direct paid billings with this payment method
            $gatewayAutoBillings = $paidAutoBillings->where('payment_method', $gateway);
            $gatewayManualBillings = $paidManualBillings->where('payment_method', $gateway);
            
            $totalAmount = $gatewayPayments->sum('amount') 
                + $gatewayAutoBillings->sum('total_amount')
                + $gatewayManualBillings->sum('amount');
            
            $transactionCount = $gatewayPayments->count() 
                + $gatewayAutoBillings->count()
                + $gatewayManualBillings->count();
            
            // Count unique students who paid via this gateway
            $studentIds = [];
            foreach ($gatewayPayments as $payment) {
                if ($payment->billing_type === 'auto') {
                    $billing = $autoBillings->get($payment->billing_id);
                    if ($billing && $billing->student_id) {
                        $studentIds[] = $billing->student_id;
                    }
                } else {
                    $billing = $manualBillings->get($payment->billing_id);
                    if ($billing && $billing->student_ids) {
                        $studentIds = array_merge($studentIds, $billing->student_ids ?? []);
                    }
                }
            }
            
            // Add students from direct paid billings
            foreach ($gatewayAutoBillings as $billing) {
                if ($billing->student_id) {
                    $studentIds[] = $billing->student_id;
                }
            }
            foreach ($gatewayManualBillings as $billing) {
                if ($billing->student_ids) {
                    $studentIds = array_merge($studentIds, $billing->student_ids ?? []);
                }
            }
            
            $uniqueStudents = count(array_unique($studentIds));

            // Group by currency (from payments and direct billings)
            $currencyBreakdown = [];
            foreach ($gatewayPayments->groupBy('currency') as $currency => $payments) {
                $currencyBreakdown[$currency] = [
                    'amount' => ($currencyBreakdown[$currency]['amount'] ?? 0) + $payments->sum('amount'),
                    'transactions' => ($currencyBreakdown[$currency]['transactions'] ?? 0) + $payments->count(),
                ];
            }
            
            // Add currency breakdown from direct paid billings
            foreach ($gatewayAutoBillings->groupBy(function($billing) {
                return is_object($billing->currency) ? $billing->currency->value : $billing->currency;
            }) as $currency => $billings) {
                $currencyBreakdown[$currency] = [
                    'amount' => ($currencyBreakdown[$currency]['amount'] ?? 0) + $billings->sum('total_amount'),
                    'transactions' => ($currencyBreakdown[$currency]['transactions'] ?? 0) + $billings->count(),
                ];
            }
            
            foreach ($gatewayManualBillings->groupBy(function($billing) {
                return is_object($billing->currency) ? $billing->currency->value : $billing->currency;
            }) as $currency => $billings) {
                $currencyBreakdown[$currency] = [
                    'amount' => ($currencyBreakdown[$currency]['amount'] ?? 0) + $billings->sum('amount'),
                    'transactions' => ($currencyBreakdown[$currency]['transactions'] ?? 0) + $billings->count(),
                ];
            }

            $gatewayStats[$gateway] = [
                'total_amount' => round($totalAmount, 2),
                'students_count' => $uniqueStudents,
                'transactions_count' => $transactionCount,
                'currency_breakdown' => $currencyBreakdown,
            ];
        }

        return $gatewayStats;
    }

    /**
     * Calculate statistics grouped by currency
     */
    private function calculateCurrencyStatistics($paidPayments, $paidAutoBillings, $paidManualBillings, $unpaidAutoBillings, $unpaidManualBillings): array
    {
        $currencies = ['USD', 'GBP', 'EUR', 'EGP', 'SAR', 'AED', 'CAD'];
        $currencyStats = [];

        foreach ($currencies as $currency) {
            // Paid amounts for this currency (from BillingPayment table)
            $paidAmount = $paidPayments->where('currency', $currency)->sum('amount');
            
            // Add paid amounts from direct paid billings
            $paidAmount += $paidAutoBillings->filter(function($billing) use ($currency) {
                $billingCurrency = is_object($billing->currency) ? $billing->currency->value : $billing->currency;
                return $billingCurrency === $currency;
            })->sum('total_amount');
            
            $paidAmount += $paidManualBillings->filter(function($billing) use ($currency) {
                $billingCurrency = is_object($billing->currency) ? $billing->currency->value : $billing->currency;
                return $billingCurrency === $currency;
            })->sum('amount');
            
            // Unpaid auto billings for this currency
            $unpaidAutoAmount = $unpaidAutoBillings->where('currency', $currency)->sum('total_amount');
            
            // Unpaid manual billings for this currency
            $unpaidManualAmount = $unpaidManualBillings->where('currency', $currency)->sum('amount');
            
            $totalRemaining = $unpaidAutoAmount + $unpaidManualAmount;

            // Count paid students for this currency
            $paidStudentIds = [];
            
            // From BillingPayment records
            $currencyPayments = $paidPayments->where('currency', $currency);
            $currencyAutoBillingIds = $currencyPayments->where('billing_type', 'auto')->pluck('billing_id')->unique();
            $currencyManualBillingIds = $currencyPayments->where('billing_type', 'manual')->pluck('billing_id')->unique();
            
            $currencyAutoBillings = AutoBilling::whereIn('id', $currencyAutoBillingIds)->get()->keyBy('id');
            $currencyManualBillings = ManualBilling::whereIn('id', $currencyManualBillingIds)->get()->keyBy('id');
            
            foreach ($currencyPayments as $payment) {
                if ($payment->billing_type === 'auto') {
                    $billing = $currencyAutoBillings->get($payment->billing_id);
                    if ($billing && $billing->student_id) {
                        $paidStudentIds[] = $billing->student_id;
                    }
                } else {
                    $billing = $currencyManualBillings->get($payment->billing_id);
                    if ($billing && $billing->student_ids) {
                        $paidStudentIds = array_merge($paidStudentIds, $billing->student_ids ?? []);
                    }
                }
            }
            
            // From direct paid billings
            foreach ($paidAutoBillings as $billing) {
                $billingCurrency = is_object($billing->currency) ? $billing->currency->value : $billing->currency;
                if ($billingCurrency === $currency && $billing->student_id) {
                    $paidStudentIds[] = $billing->student_id;
                }
            }
            
            foreach ($paidManualBillings as $billing) {
                $billingCurrency = is_object($billing->currency) ? $billing->currency->value : $billing->currency;
                if ($billingCurrency === $currency && $billing->student_ids) {
                    $paidStudentIds = array_merge($paidStudentIds, $billing->student_ids ?? []);
                }
            }
            
            $paidStudentsCount = count(array_unique($paidStudentIds));

            // Count unpaid students for this currency
            $unpaidStudentIds = [];
            foreach ($unpaidAutoBillings->where('currency', $currency) as $billing) {
                if ($billing->student_id) {
                    $unpaidStudentIds[] = $billing->student_id;
                }
            }
            foreach ($unpaidManualBillings->where('currency', $currency) as $billing) {
                if ($billing->student_ids) {
                    $unpaidStudentIds = array_merge($unpaidStudentIds, $billing->student_ids ?? []);
                }
            }
            $unpaidStudentsCount = count(array_unique($unpaidStudentIds));

            $currencyStats[$currency] = [
                'collected' => round($paidAmount, 2),
                'remaining' => round($totalRemaining, 2),
                'paid_students' => $paidStudentsCount,
                'unpaid_students' => $unpaidStudentsCount,
            ];
        }

        return $currencyStats;
    }

    /**
     * Calculate overall statistics
     */
    private function calculateOverallStatistics($paidPayments, $paidAutoBillings, $paidManualBillings, $unpaidAutoBillings, $unpaidManualBillings): array
    {
        // Get year and month from request or use current date
        $year = request()->input('year', now()->year);
        $month = request()->input('month', now()->month);
        
        $totalCollected = $paidPayments->sum('amount')
            + $paidAutoBillings->sum('total_amount')
            + $paidManualBillings->sum('amount');
        
        $totalRemaining = $unpaidAutoBillings->sum('total_amount') + 
                         $unpaidManualBillings->sum('amount');
        
        $totalTransactions = $paidPayments->count()
            + $paidAutoBillings->count()
            + $paidManualBillings->count();
        $averageTransaction = $totalTransactions > 0 ? $totalCollected / $totalTransactions : 0;

        // Calculate total billings (paid + unpaid) for success rate
        $totalAutoBillings = AutoBilling::where('year', $year)
            ->where('month', $month)
            ->count();
        
        $totalManualBillings = ManualBilling::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
        
        $totalBillings = $totalAutoBillings + $totalManualBillings;
        $successRate = $totalBillings > 0 ? ($totalTransactions / $totalBillings) * 100 : 0;

        return [
            'total_collected' => round($totalCollected, 2),
            'total_remaining' => round($totalRemaining, 2),
            'payment_success_rate' => round($successRate, 2),
            'average_transaction' => round($averageTransaction, 2),
            'total_transactions' => $totalTransactions,
        ];
    }
}
