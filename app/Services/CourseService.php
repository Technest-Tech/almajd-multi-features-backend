<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Course::with(['student', 'teacher'])
            ->withCount('lessons');

        // Filter by student
        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        // Filter by teacher
        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        // Search by name
        if (isset($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): ?Course
    {
        return Course::with(['student', 'teacher'])
            ->withCount('lessons')
            ->find($id);
    }

    public function create(array $data): Course
    {
        return Course::create($data);
    }

    public function update(Course $course, array $data): Course
    {
        $course->update($data);
        return $course->fresh()->load(['student', 'teacher']);
    }

    public function delete(Course $course): bool
    {
        return $course->delete();
    }
}

