<?php

namespace App\Services;

use App\Models\CalendarStudent;
use Illuminate\Pagination\LengthAwarePaginator;

class CalendarStudentService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = CalendarStudent::query();

        // Search by name
        if (isset($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        // Sort by created_at descending by default
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): ?CalendarStudent
    {
        return CalendarStudent::find($id);
    }

    public function create(array $data): CalendarStudent
    {
        return CalendarStudent::create($data);
    }

    public function update(CalendarStudent $student, array $data): CalendarStudent
    {
        $student->update($data);
        return $student->fresh();
    }

    public function delete(CalendarStudent $student): bool
    {
        return $student->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return CalendarStudent::whereIn('id', $ids)->delete();
    }

    public function search(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return CalendarStudent::where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(50)
            ->get();
    }
}

















