<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    public function getAdminStats(): array
    {
        $totalStudents = User::where('user_type', UserType::Student)->count();
        $totalTeachers = User::where('user_type', UserType::Teacher)->count();

        // Dashboard resets every month: all lesson-based stats are for current month only
        $now = Carbon::now();
        $startOfCurrentMonth = $now->copy()->startOfMonth();
        $endOfCurrentMonth = $now->copy()->endOfMonth();

        // Calculate total hours from lessons for current month only
        $totalHours = Lesson::whereBetween('date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->whereNotNull('duration')
            ->sum(DB::raw('duration')) / 60; // Convert minutes to hours

        // Calculate profit by currency for current month only
        $profitByCurrencyCollection = Lesson::query()
            ->join('courses', 'lessons.course_id', '=', 'courses.id')
            ->join('users as students', 'courses.student_id', '=', 'students.id')
            ->whereBetween('lessons.date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->select(
                'students.currency',
                DB::raw('SUM(lessons.duration / 60 * students.hour_price) as total_profit')
            )
            ->whereNotNull('students.currency')
            ->groupBy('students.currency')
            ->get();

        $profitByCurrency = [];
        foreach ($profitByCurrencyCollection as $item) {
            // Handle currency enum or string
            $currencyValue = $item->currency instanceof \App\Enums\Currency
                ? $item->currency->value
                : (string) $item->currency;
            $profitByCurrency[$currencyValue] = (float) $item->total_profit;
        }

        // Calculate salaries in EGP for current month only
        $totalSalariesEGP = Lesson::query()
            ->join('courses', 'lessons.course_id', '=', 'courses.id')
            ->join('users as teachers', 'courses.teacher_id', '=', 'teachers.id')
            ->where('teachers.user_type', UserType::Teacher)
            ->whereNotNull('teachers.hour_price')
            ->whereBetween('lessons.date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->select(DB::raw('SUM(lessons.duration / 60 * COALESCE(lessons.duty, teachers.hour_price)) as total_salary'))
            ->value('total_salary') ?? 0.0;

        // All salaries are in EGP, so totals are the same
        $totalSalaries = $totalSalariesEGP;

        // For backward compatibility, set salaries_by_currency with only EGP
        $salariesByCurrency = ['EGP' => $totalSalariesEGP];

        // Calculate net profit (total profit - total salaries) for current month
        $totalProfit = array_sum($profitByCurrency);
        $netProfit = $totalProfit - $totalSalaries;

        return [
            'month' => (int) $now->month,
            'year' => (int) $now->year,
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_hours' => round($totalHours, 2),
            'total_salaries' => round($totalSalaries, 2),
            'total_salaries_egp' => round($totalSalariesEGP, 2),
            'salaries_by_currency' => $salariesByCurrency,
            'profit_by_currency' => $profitByCurrency,
            'total_profit' => round($totalProfit, 2),
            'net_profit' => round($netProfit, 2),
        ];
    }

    public function getTeacherStats(int $teacherId): array
    {
        $teacher = User::findOrFail($teacherId);
        
        if (!$teacher->isTeacher()) {
            throw new \InvalidArgumentException('User is not a teacher');
        }

        $assignedStudentsCount = $teacher->assignedStudents()->count();

        // Hours this month: current month only (dashboard resets every month)
        $now = Carbon::now();
        $startOfCurrentMonth = $now->copy()->startOfMonth();
        $endOfCurrentMonth = $now->copy()->endOfMonth();

        // Debug: Check if teacher has any courses
        $coursesCount = Course::where('teacher_id', $teacherId)->count();

        // Debug: Check if teacher has any lessons at all (without date filter)
        $allLessonsCount = Lesson::join('courses', 'lessons.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->count();

        // Current month only: lessons between start and end of month
        $totalMinutes = Lesson::join('courses', 'lessons.course_id', '=', 'courses.id')
            ->where('courses.teacher_id', $teacherId)
            ->whereBetween('lessons.date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->whereNotNull('lessons.duration')
            ->sum('lessons.duration');

        // Debug logging
        Log::info("Teacher Stats Debug", [
            'teacher_id' => $teacherId,
            'courses_count' => $coursesCount,
            'all_lessons_count' => $allLessonsCount,
            'start_of_current_month' => $startOfCurrentMonth->format('Y-m-d'),
            'end_of_current_month' => $endOfCurrentMonth->format('Y-m-d'),
            'total_minutes' => $totalMinutes,
        ]);

        $hoursThisMonth = ($totalMinutes ?? 0) / 60;

        // Calculate profit for this month only
        $totalProfit = 0;

        if ($teacher->hour_price !== null) {
            $totalProfit = $hoursThisMonth * (float) $teacher->hour_price;
        } else {
            $totalProfit = Lesson::join('courses', 'lessons.course_id', '=', 'courses.id')
                ->where('courses.teacher_id', $teacherId)
                ->whereBetween('lessons.date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
                ->whereNotNull('lessons.duty')
                ->sum('lessons.duty') ?? 0;
        }

        return [
            'month' => (int) $now->month,
            'year' => (int) $now->year,
            'assigned_students_count' => $assignedStudentsCount,
            'hours_this_month' => round($hoursThisMonth, 2),
            'total_profit' => round($totalProfit, 2),
            'courses_count' => $coursesCount,
        ];
    }
}

