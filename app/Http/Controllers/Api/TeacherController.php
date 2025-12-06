<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\User;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function __construct(
        private TeacherService $teacherService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'per_page']);
        $teachers = $this->teacherService->getAll($filters);

        return response()->json($teachers);
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        try {
            $teacher = $this->teacherService->create($request->validated());
            return response()->json($teacher, 201);
        } catch (\Exception $e) {
            Log::error('Error creating teacher: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create teacher'], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $teacher = $this->teacherService->getById($id);

        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }

        return response()->json($teacher);
    }

    public function update(UpdateTeacherRequest $request, string $id): JsonResponse
    {
        $teacher = $this->teacherService->getById($id);

        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }

        try {
            $teacher = $this->teacherService->update($teacher, $request->validated());
            return response()->json($teacher);
        } catch (\Exception $e) {
            Log::error('Error updating teacher: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update teacher'], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $teacher = $this->teacherService->getById($id);

        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }

        $this->teacherService->delete($teacher);

        return response()->json(['message' => 'Teacher deleted successfully']);
    }

    public function getAssignedStudents(string $id): JsonResponse
    {
        $teacher = $this->teacherService->getById($id);

        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }

        $students = $this->teacherService->getAssignedStudents($teacher);

        return response()->json($students);
    }

    public function assignStudents(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $teacher = $this->teacherService->getById($id);

        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }

        $this->teacherService->assignStudents($teacher, $request->student_ids);

        return response()->json(['message' => 'Students assigned successfully']);
    }
}
