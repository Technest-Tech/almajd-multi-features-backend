<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\AutoBilling;
use App\Models\Lesson;
use App\Models\ManualBilling;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate auto billings from lessons for a specific year and month
     */
    public function generateAutoBillings(int $year, int $month): array
    {
        // Get all students
        $students = User::where('user_type', UserType::Student)
            ->whereNotNull('hour_price')
            ->whereNotNull('currency')
            ->get();

        $generated = [];

        foreach ($students as $student) {
            // Get lessons for this student in the specified month/year
            // All lessons are 'present' by default, so no status filter needed
            $lessons = Lesson::whereHas('course', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

            if ($lessons->isEmpty()) {
                continue;
            }

            // Calculate total hours (duration is in minutes, convert to hours)
            $totalMinutes = $lessons->sum('duration');
            $totalHours = $totalMinutes / 60;

            // Calculate billing: total_hours * student.hour_price
            $totalAmount = $totalHours * (float) $student->hour_price;

            // Get or create billing
            $billing = AutoBilling::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'year' => $year,
                    'month' => $month,
                ],
                [
                    'total_hours' => round($totalHours, 2),
                    'total_amount' => round($totalAmount, 2),
                    'currency' => $student->currency,
                    'is_paid' => false,
                ]
            );

            // Update if billing already exists
            if ($billing->wasRecentlyCreated === false) {
                $billing->update([
                    'total_hours' => round($totalHours, 2),
                    'total_amount' => round($totalAmount, 2),
                ]);
            }

            // Generate payment token if not exists
            if (!$billing->payment_token) {
                $billing->generatePaymentToken();
            }

            $generated[] = $billing;
        }

        return $generated;
    }

    /**
     * Get auto billings with filters
     */
    public function getAutoBillings(int $year, int $month, ?bool $isPaid = null)
    {
        $query = AutoBilling::with('student')
            ->where('year', $year)
            ->where('month', $month);

        if ($isPaid !== null) {
            $query->where('is_paid', $isPaid);
        }

        return $query->get();
    }

    /**
     * Get totals for auto billings
     */
    public function getAutoBillingsTotals(int $year, int $month): array
    {
        $unpaidQuery = AutoBilling::where('year', $year)
            ->where('month', $month)
            ->where('is_paid', false);
        
        $unpaidCount = $unpaidQuery->count();
        
        $unpaid = $unpaidQuery
            ->selectRaw('currency, SUM(total_amount) as total')
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(function ($item) {
                // Convert currency enum to string value for array key
                $currencyValue = $item->currency instanceof \App\Enums\Currency 
                    ? $item->currency->value 
                    : (string) $item->currency;
                return [$currencyValue => (float) $item->total];
            })
            ->toArray();

        $paidQuery = AutoBilling::where('year', $year)
            ->where('month', $month)
            ->where('is_paid', true);
        
        $paidCount = $paidQuery->count();
        
        $paid = $paidQuery
            ->selectRaw('currency, SUM(total_amount) as total')
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(function ($item) {
                // Convert currency enum to string value for array key
                $currencyValue = $item->currency instanceof \App\Enums\Currency 
                    ? $item->currency->value 
                    : (string) $item->currency;
                return [$currencyValue => (float) $item->total];
            })
            ->toArray();

        // Ensure we always return objects (associative arrays) even when empty
        // This prevents JSON encoding issues where empty arrays become [] instead of {}
        return [
            'unpaid' => empty($unpaid) ? (object)[] : $unpaid,
            'paid' => empty($paid) ? (object)[] : $paid,
            'unpaid_count' => $unpaidCount,
            'paid_count' => $paidCount,
        ];
    }

    /**
     * Mark billing as paid
     */
    public function markAsPaid(int $billingId, string $type, ?string $paymentMethod = null): bool
    {
        if ($type === 'auto') {
            $billing = AutoBilling::findOrFail($billingId);
        } else {
            $billing = ManualBilling::findOrFail($billingId);
        }

        $billing->update([
            'is_paid' => true,
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
        ]);

        return true;
    }

    /**
     * Generate payment link for billing
     */
    public function generatePaymentLink(int $billingId, string $type): string
    {
        if ($type === 'auto') {
            $billing = AutoBilling::findOrFail($billingId);
        } else {
            $billing = ManualBilling::findOrFail($billingId);
        }

        if (!$billing->payment_token) {
            $billing->generatePaymentToken();
        }

        return url("/pay/{$billing->payment_token}");
    }
}
