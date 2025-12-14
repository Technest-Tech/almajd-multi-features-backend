<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TeacherService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = User::where('user_type', UserType::Teacher)
            ->withCount('assignedStudents');

        // Search by name or email
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): ?User
    {
        return User::where('user_type', UserType::Teacher)
            ->with('assignedStudents')
            ->find($id);
    }

    public function create(array $data): User
    {
        $data['user_type'] = UserType::Teacher;
        $data['password'] = bcrypt($data['password']);
        // All teachers must use EGP currency
        $data['currency'] = 'EGP';

        $teacher = User::create($data);

        // Assign students if provided
        if (isset($data['student_ids']) && is_array($data['student_ids'])) {
            $teacher->assignedStudents()->sync($data['student_ids']);
        }

        return $teacher->load('assignedStudents');
    }

    public function update(User $teacher, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        // All teachers must use EGP currency
        $data['currency'] = 'EGP';

        $teacher->update($data);

        // Update student assignments if provided
        if (isset($data['student_ids']) && is_array($data['student_ids'])) {
            $teacher->assignedStudents()->sync($data['student_ids']);
        }

        return $teacher->fresh()->load('assignedStudents');
    }

    public function delete(User $teacher): bool
    {
        return $teacher->delete();
    }

    public function getAssignedStudents(User $teacher): Collection
    {
        return $teacher->assignedStudents;
    }

    public function assignStudents(User $teacher, array $studentIds): void
    {
        $teacher->assignedStudents()->sync($studentIds);
    }
}

