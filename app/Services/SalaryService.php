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
     * @return array
     */
    public function getTeacherSalaries(int $year, int $month): array
    {
        // Get all teachers
        $teachers = User::where('user_type', UserType::Teacher)
            ->whereNotNull('hour_price')
            ->get();

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

            // Calculate salary: total_hours * teacher.hour_price
            $salary = $totalHours * (float) $teacher->hour_price;

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
                'hour_price' => (float) $teacher->hour_price,
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
