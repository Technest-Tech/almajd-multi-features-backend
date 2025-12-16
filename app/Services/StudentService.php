<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = User::where('user_type', UserType::Student);

        // Search by name or email
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by country
        if (isset($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        // Filter by currency
        if (isset($filters['currency'])) {
            $query->where('currency', $filters['currency']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): ?User
    {
        return User::where('user_type', UserType::Student)->find($id);
    }

    public function create(array $data): User
    {
        $data['user_type'] = UserType::Student;
        $data['password'] = bcrypt($data['password'] ?? 'password'); // Default password if not provided
        
        // Generate random email if not provided
        if (!isset($data['email']) || empty($data['email'])) {
            $data['email'] = $this->generateRandomEmail();
        }
        
        return User::create($data);
    }
    
    /**
     * Generate a unique random email for a student
     */
    private function generateRandomEmail(): string
    {
        do {
            $randomString = bin2hex(random_bytes(8));
            $email = "student_{$randomString}@almajdacademy.local";
        } while (User::where('email', $email)->exists());
        
        return $email;
    }

    public function update(User $student, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $student->update($data);
        return $student->fresh();
    }

    public function delete(User $student): bool
    {
        return $student->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return User::where('user_type', UserType::Student)
            ->whereIn('id', $ids)
            ->delete();
    }

    public function export(array $filters = []): Collection
    {
        $query = User::where('user_type', UserType::Student);

        if (isset($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (isset($filters['currency'])) {
            $query->where('currency', $filters['currency']);
        }

        return $query->get();
    }
}

