<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    /**
     * Get teacher salaries for a specific year and month
     * 
     * @param int $year
     * @param int $month
     * @param float|null $unifiedHourPrice Optional unified hour price for teachers without specific price
     * @return array
     */
    public function getTeacherSalaries(int $year, int $month, ?float $unifiedHourPrice = null): array
    {
        // Get all teachers with hour_price
        $teachersQuery = User::where('user_type', UserType::Teacher);
        
        if ($unifiedHourPrice !== null) {
            // Include teachers without hour_price when unified price is provided
            $teachersQuery->where(function ($query) {
                $query->whereNotNull('hour_price')
                      ->orWhereNull('hour_price');
            });
        } else {
            // Only get teachers with hour_price
            $teachersQuery->whereNotNull('hour_price');
        }
        
        $teachers = $teachersQuery->get();

        $salaries = [];
        $totalsByCurrency = [];

        foreach ($teachers as $teacher) {
            // Get lessons for this teacher in the specified month/year
            // All lessons are 'present' by default, so no status filter needed
            $lessons = Lesson::whereHas('course', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
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

            // Determine hour price: use teacher's price if available, otherwise use unified price
            $hourPrice = $teacher->hour_price ?? $unifiedHourPrice;
            
            // Skip if no hour price available
            if ($hourPrice === null) {
                continue;
            }

            // Calculate salary: total_hours * hour_price
            $salary = $totalHours * (float) $hourPrice;

            // All teachers use EGP currency
            $currency = 'EGP';

            $salaries[] = [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'teacher_email' => $teacher->email,
                'currency' => $currency,
                'total_hours' => round($totalHours, 2),
                'salary' => round($salary, 2),
                'lessons_count' => $lessons->count(),
                'hour_price' => (float) $hourPrice,
            ];

            // Accumulate totals in EGP only
            if (!isset($totalsByCurrency[$currency])) {
                $totalsByCurrency[$currency] = 0;
            }
            $totalsByCurrency[$currency] += $salary;
        }

        // Round totals
        foreach ($totalsByCurrency as $currency => $total) {
            $totalsByCurrency[$currency] = round($total, 2);
        }

        return [
            'year' => $year,
            'month' => $month,
            'salaries' => $salaries,
            'totals_by_currency' => $totalsByCurrency,
        ];
    }
}
