<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Get all teachers who have lessons in the specified month/year
        // This ensures we don't miss any teachers with lessons, even if they don't have hour_price
        $teacherIdsWithLessons = Lesson::join('courses', 'lessons.course_id', '=', 'courses.id')
            ->join('users', 'courses.teacher_id', '=', 'users.id')
            ->where('users.user_type', UserType::Teacher)
            ->whereYear('lessons.date', $year)
            ->whereMonth('lessons.date', $month)
            ->whereNotNull('lessons.duration')
            ->distinct()
            ->pluck('users.id')
            ->toArray();

        // Debug logging
        Log::info("SalaryService Debug", [
            'year' => $year,
            'month' => $month,
            'teacher_ids_with_lessons' => $teacherIdsWithLessons,
            'unified_hour_price' => $unifiedHourPrice,
        ]);

        if (empty($teacherIdsWithLessons)) {
            return [
                'year' => $year,
                'month' => $month,
                'salaries' => [],
                'totals_by_currency' => (object)[],
            ];
        }

        // Get all teachers who have lessons
        $teachers = User::whereIn('id', $teacherIdsWithLessons)
            ->where('user_type', UserType::Teacher)
            ->get();

        $salaries = [];
        $totalsByCurrency = []; // Associative array (will be encoded as object in JSON)

        foreach ($teachers as $teacher) {
            // Get lessons for this teacher in the specified month/year using join for better performance
            // All lessons are 'present' by default, so no status filter needed
            $totalMinutes = Lesson::join('courses', 'lessons.course_id', '=', 'courses.id')
                ->where('courses.teacher_id', $teacher->id)
                ->whereYear('lessons.date', $year)
                ->whereMonth('lessons.date', $month)
                ->whereNotNull('lessons.duration')
                ->sum('lessons.duration');

            // Skip if no lessons found (shouldn't happen, but safety check)
            if ($totalMinutes === null || $totalMinutes == 0) {
                Log::debug("Teacher {$teacher->id} ({$teacher->name}) has no lessons for {$year}-{$month}");
                continue;
            }

            // Calculate total hours (duration is in minutes, convert to hours)
            $totalHours = $totalMinutes / 60;

            // Determine hour price: use teacher's price if available, otherwise use unified price
            $hourPrice = $teacher->hour_price ?? $unifiedHourPrice;
            
            // Skip if no hour price available
            if ($hourPrice === null) {
                Log::debug("Teacher {$teacher->id} ({$teacher->name}) has no hour_price and no unified price provided");
                continue;
            }
            
            // Get lesson count for reporting
            $lessonsCount = Lesson::join('courses', 'lessons.course_id', '=', 'courses.id')
                ->where('courses.teacher_id', $teacher->id)
                ->whereYear('lessons.date', $year)
                ->whereMonth('lessons.date', $month)
                ->count();

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
                'lessons_count' => $lessonsCount,
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
        
        // If empty, convert to object to ensure JSON encodes as {} not []
        if (empty($totalsByCurrency)) {
            $totalsByCurrency = (object)[];
        }

        return [
            'year' => $year,
            'month' => $month,
            'salaries' => $salaries,
            'totals_by_currency' => $totalsByCurrency,
        ];
    }
}
