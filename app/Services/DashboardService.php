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
        
        // Calculate total hours from lessons (all lessons are 'present' by default)
        $totalHours = Lesson::sum(DB::raw('duration')) / 60; // Convert minutes to hours

        // Calculate profit by currency (all lessons are 'present' by default)
        $profitByCurrencyCollection = Lesson::query()
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
            // Handle currency enum or string
            $currencyValue = $item->currency instanceof \App\Enums\Currency 
                ? $item->currency->value 
                : (string) $item->currency;
            $profitByCurrency[$currencyValue] = (float) $item->total_profit;
        }

        // Calculate ALL salaries in EGP (all lessons are 'present' by default)
        // All teachers use EGP currency, so we don't need to group by currency
        $totalSalariesEGP = Lesson::query()
            ->join('courses', 'lessons.course_id', '=', 'courses.id')
            ->join('users as teachers', 'courses.teacher_id', '=', 'teachers.id')
            ->where('teachers.user_type', UserType::Teacher)
            ->whereNotNull('teachers.hour_price')
            ->select(DB::raw('SUM(lessons.duration / 60 * COALESCE(lessons.duty, teachers.hour_price)) as total_salary'))
            ->value('total_salary') ?? 0.0;
        
        // All salaries are in EGP, so totals are the same
        $totalSalaries = $totalSalariesEGP;
        
        // For backward compatibility, set salaries_by_currency with only EGP
        $salariesByCurrency = ['EGP' => $totalSalariesEGP];

        // Calculate net profit (total profit - total salaries)
        $totalProfit = array_sum($profitByCurrency);
        $netProfit = $totalProfit - $totalSalaries;

        return [
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

        // Hours this month (all lessons are 'present' by default)
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Calculate total hours with proper NULL handling (sum returns NULL if no rows)
        $totalMinutes = Lesson::whereHas('course', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('duration');
        
        $hoursThisMonth = ($totalMinutes ?? 0) / 60;

        // Calculate profit for this month
        // If teacher has fixed hour_price, calculate: hours * hour_price
        // Otherwise, sum duty from lessons for this month
        $totalProfit = 0;
        
        if ($teacher->hour_price !== null) {
            // Use fixed hour price: hours * hour_price
            $totalProfit = $hoursThisMonth * (float) $teacher->hour_price;
        } else {
            // Fallback to summing duty from lessons for this month
            $totalProfit = Lesson::whereHas('course', function ($query) use ($teacherId) {
                    $query->where('teacher_id', $teacherId);
                })
                ->whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->sum('duty') ?? 0;
        }

        return [
            'assigned_students_count' => $assignedStudentsCount,
            'hours_this_month' => round($hoursThisMonth, 2),
            'total_profit' => round($totalProfit, 2),
        ];
    }
}

