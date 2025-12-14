<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\AutoBilling;
use App\Models\User;
use App\Enums\Currency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    /**
     * Get lessons for a specific student within a date range
     */
    public function getStudentLessons(int $studentId, string $fromDate, string $toDate): Collection
    {
        return Lesson::with(['course.student', 'course.teacher'])
            ->whereHas('course', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get lessons for multiple students within a date range
     */
    public function getMultipleStudentsLessons(array $studentIds, string $fromDate, string $toDate): Collection
    {
        return Lesson::with(['course.student', 'course.teacher'])
            ->whereHas('course', function ($query) use ($studentIds) {
                $query->whereIn('student_id', $studentIds);
            })
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get academy statistics for a date range
     * Returns: total revenue by currency, total collected, total remaining, and all lessons
     */
    public function getAcademyStatistics(string $fromDate, string $toDate): array
    {
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);

        // Get all lessons in date range, ordered by student name
        $lessons = Lesson::with(['course.student', 'course.teacher'])
            ->whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->sortBy(function ($lesson) {
                return $lesson->course?->student?->name ?? '';
            })
            ->values();

        // Get all auto billings in the date range
        $billings = AutoBilling::where(function ($query) use ($from, $to) {
            // Check if billing month/year falls within the date range
            $query->where(function ($q) use ($from, $to) {
                $q->whereYear('created_at', '>=', $from->year)
                  ->whereMonth('created_at', '>=', $from->month)
                  ->whereYear('created_at', '<=', $to->year)
                  ->whereMonth('created_at', '<=', $to->month);
            });
        })->get();

        // Calculate total revenue by currency (from lessons)
        $revenueByCurrency = [];
        foreach ($lessons as $lesson) {
            $lessonCost = $this->calculateLessonCost($lesson);
            if ($lessonCost > 0 && $lesson->course?->student?->currency) {
                $currency = $lesson->course->student->currency;
                $currencyKey = $currency->value;
                if (!isset($revenueByCurrency[$currencyKey])) {
                    $revenueByCurrency[$currencyKey] = [
                        'currency' => $currency,
                        'amount' => 0,
                    ];
                }
                $revenueByCurrency[$currencyKey]['amount'] += $lessonCost;
            }
        }

        // Calculate total collected (paid billings)
        $totalCollected = [];
        foreach (Currency::cases() as $currency) {
            $totalCollected[$currency->value] = $billings
                ->where('is_paid', true)
                ->where('currency', $currency)
                ->sum('total_amount');
        }

        // Calculate total remaining (unpaid billings)
        $totalRemaining = [];
        foreach (Currency::cases() as $currency) {
            $totalRemaining[$currency->value] = $billings
                ->where('is_paid', false)
                ->where('currency', $currency)
                ->sum('total_amount');
        }

        return [
            'lessons' => $lessons,
            'revenue_by_currency' => $revenueByCurrency,
            'total_collected' => $totalCollected,
            'total_remaining' => $totalRemaining,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    }

    /**
     * Calculate total cost for lessons
     * Cost is calculated as: (duration in minutes / 60) * student.hour_price
     */
    public function calculateTotalCost(Collection $lessons): float
    {
        $total = 0;
        foreach ($lessons as $lesson) {
            if ($lesson->course?->student?->hour_price && $lesson->duration) {
                $hours = $lesson->duration / 60;
                $total += $hours * (float) $lesson->course->student->hour_price;
            } elseif ($lesson->duty) {
                // Fallback to duty if it exists
                $total += (float) $lesson->duty;
            }
        }
        return round($total, 2);
    }

    /**
     * Calculate cost for a single lesson
     * Cost is calculated as: (duration in minutes / 60) * student.hour_price
     */
    public function calculateLessonCost($lesson): float
    {
        if ($lesson->course?->student?->hour_price && $lesson->duration) {
            $hours = $lesson->duration / 60;
            return round($hours * (float) $lesson->course->student->hour_price, 2);
        } elseif ($lesson->duty) {
            // Fallback to duty if it exists
            return (float) $lesson->duty;
        }
        return 0.00;
    }

    /**
     * Get student by ID
     */
    public function getStudent(int $studentId): ?User
    {
        return User::find($studentId);
    }

    /**
     * Get multiple students by IDs
     */
    public function getStudents(array $studentIds): Collection
    {
        return User::whereIn('id', $studentIds)->get();
    }
}
