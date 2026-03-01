<?php

namespace App\Services;

use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseService
{
    /**
     * Current month date range for lesson counts (teacher app: all data is current month only).
     */
    private function currentMonthRange(): array
    {
        $now = Carbon::now();
        return [$now->copy()->startOfMonth()->format('Y-m-d'), $now->copy()->endOfMonth()->format('Y-m-d')];
    }

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        [$start, $end] = $this->currentMonthRange();
        $query = Course::with(['student', 'teacher'])
            ->withCount(['lessons' => fn ($q) => $q->whereBetween('date', [$start, $end])]);

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
        [$start, $end] = $this->currentMonthRange();
        return Course::with(['student', 'teacher'])
            ->withCount(['lessons' => fn ($q) => $q->whereBetween('date', [$start, $end])])
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

