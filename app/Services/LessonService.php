<?php

namespace App\Services;

use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LessonService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Lesson::with(['course.student', 'course.teacher', 'createdBy']);

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
        $date = Carbon::parse($data['date']);
        $today = Carbon::today();
        
        if ($date->lt($today)) {
            throw new \InvalidArgumentException('Lesson date must be today or in the future');
        }

        return Lesson::create($data);
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        // Validate date is today or future if date is being updated
        if (isset($data['date'])) {
            $date = Carbon::parse($data['date']);
            $today = Carbon::today();
            
            if ($date->lt($today)) {
                throw new \InvalidArgumentException('Lesson date must be today or in the future');
            }
        }

        $lesson->update($data);
        return $lesson->fresh()->load(['course.student', 'course.teacher', 'createdBy']);
    }

    public function delete(Lesson $lesson): bool
    {
        return $lesson->delete();
    }
}

