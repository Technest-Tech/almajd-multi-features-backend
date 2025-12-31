<?php

namespace App\Services;

use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\BillingService;

class LessonService
{
    public function __construct(
        private BillingService $billingService
    ) {}
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Lesson::with(['course.student', 'course.teacher', 'createdBy']);

        // Filter by teacher (if teacher_id is provided)
        if (isset($filters['teacher_id'])) {
            $query->whereHas('course', function ($q) use ($filters) {
                $q->where('teacher_id', $filters['teacher_id']);
            });
        }

        // Filter by course
        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        // Filter by year and month
        if (isset($filters['year']) && isset($filters['month'])) {
            $query->whereYear('date', $filters['year'])
                  ->whereMonth('date', $filters['month']);
        } elseif (isset($filters['year'])) {
            $query->whereYear('date', $filters['year']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): ?Lesson
    {
        return Lesson::with(['course.student', 'course.teacher', 'createdBy'])->find($id);
    }

    public function create(array $data): Lesson
    {
        // Validate date is today or future
        try {
            $date = Carbon::parse($data['date'])->startOfDay();
            $today = Carbon::today()->startOfDay();
        
        if ($date->lt($today)) {
            throw new \InvalidArgumentException('Lesson date must be today or in the future');
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid date format: ' . ($data['date'] ?? 'null'));
        }

        try {
            // Always set status to 'present' (default for all lessons)
            $data['status'] = \App\Enums\LessonStatus::Present;
            
            $lesson = Lesson::create($data);
            
            // Load relationships needed for billing generation
            $lesson->load('course.student', 'course.teacher');
            
            // Auto-generate/update billing for the student (increments existing billing or creates new)
            // This ensures student billing is updated immediately when lesson is added
            $this->generateBillingForLesson($lesson);
            
            // Note: Teacher salaries are calculated on-demand from lessons, so they automatically
            // reflect new lessons when the salary endpoint is called. No storage needed.
            // Dashboard statistics are also calculated on-demand from lessons, so they
            // automatically reflect new lessons when dashboard is loaded.
            
            return $lesson;
        } catch (\Exception $e) {
            throw new \Exception('Database error: ' . $e->getMessage());
        }
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        $oldDate = $lesson->date;
        
        // Validate date is today or future if date is being updated
        if (isset($data['date'])) {
            $date = Carbon::parse($data['date']);
            $today = Carbon::today();
            
            if ($date->lt($today)) {
                throw new \InvalidArgumentException('Lesson date must be today or in the future');
            }
        }

        // Always keep status as 'present' (status cannot be changed)
        unset($data['status']);
        
        $lesson->update($data);
        $updatedLesson = $lesson->fresh()->load(['course.student', 'course.teacher', 'createdBy']);
        
        // Regenerate billing if date or duration changed (affects billing amount)
        if (isset($data['date']) || isset($data['duration'])) {
            // Regenerate billing for new month/year if date changed
            $this->generateBillingForLesson($updatedLesson);
            
            // Also regenerate for old month/year if date changed (to remove lesson from old billing)
            if (isset($data['date']) && $oldDate) {
                $oldYear = $oldDate->year;
                $oldMonth = $oldDate->month;
                $this->billingService->generateAutoBillings($oldYear, $oldMonth);
            }
        }
        
        // Note: Teacher salaries and dashboard stats are calculated on-demand from lessons,
        // so they automatically reflect lesson updates when endpoints are called.
        
        return $updatedLesson;
    }

    public function delete(Lesson $lesson): bool
    {
        $date = $lesson->date;
        $deleted = $lesson->delete();
        
        // Regenerate billing for the lesson's month/year after deletion
        // This removes the lesson from the student's billing
        if ($deleted && $date) {
            $this->billingService->generateAutoBillings($date->year, $date->month);
        }
        
        // Note: Teacher salaries and dashboard stats are calculated on-demand from lessons,
        // so they automatically reflect lesson deletion when endpoints are called.
        
        return $deleted;
    }

    /**
     * Generate billing for a specific lesson's month/year
     * All lessons are 'present' by default, so always generate billing
     */
    private function generateBillingForLesson(Lesson $lesson): void
    {
        if (!$lesson->course || !$lesson->course->student) {
            return;
        }

        $date = Carbon::parse($lesson->date);
        $year = $date->year;
        $month = $date->month;

        // All lessons are 'present', so always generate billing
        $this->billingService->generateAutoBillings($year, $month);
    }
}

