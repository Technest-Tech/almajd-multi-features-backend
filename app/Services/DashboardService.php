<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\Lesson;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getAdminStats(): array
    {
        $totalStudents = User::where('user_type', UserType::Student)->count();
        $totalTeachers = User::where('user_type', UserType::Teacher)->count();
        
        // Calculate total hours from completed lessons
        $totalHours = Lesson::where('status', 'completed')
            ->sum(DB::raw('duration')) / 60; // Convert minutes to hours

        // Calculate profit by currency
        $profitByCurrencyCollection = Lesson::where('status', 'completed')
            ->join('courses', 'lessons.course_id', '=', 'courses.id')
            ->join('users as students', 'courses.student_id', '=', 'students.id')
            ->select(
                'students.currency',
                DB::raw('SUM(lessons.duration / 60 * students.hour_price) as total_profit')
            )
            ->whereNotNull('students.currency')
            ->groupBy('students.currency')
            ->get();
        
        $profitByCurrency = [];
        foreach ($profitByCurrencyCollection as $item) {
            $profitByCurrency[(string) $item->currency] = (float) $item->total_profit;
        }

        // Calculate total salaries (sum of teacher hour_price * completed lesson hours)
        $totalSalaries = Lesson::where('status', 'completed')
            ->join('courses', 'lessons.course_id', '=', 'courses.id')
            ->join('users as teachers', 'courses.teacher_id', '=', 'teachers.id')
            ->select(DB::raw('SUM(lessons.duration / 60 * COALESCE(lessons.duty, teachers.hour_price)) as total'))
            ->value('total') ?? 0;

        // Calculate net profit (total profit - total salaries)
        $totalProfit = array_sum($profitByCurrency);
        $netProfit = $totalProfit - $totalSalaries;

        return [
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_hours' => round($totalHours, 2),
            'total_salaries' => round($totalSalaries, 2),
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

        // Hours this month
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $hoursThisMonth = Lesson::where('status', 'completed')
            ->whereHas('course', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum(DB::raw('duration')) / 60;

        // Total profit (sum of duty from completed lessons)
        $totalProfit = Lesson::where('status', 'completed')
            ->whereHas('course', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->sum('duty') ?? 0;

        return [
            'assigned_students_count' => $assignedStudentsCount,
            'hours_this_month' => round($hoursThisMonth, 2),
            'total_profit' => round($totalProfit, 2),
        ];
    }
}

