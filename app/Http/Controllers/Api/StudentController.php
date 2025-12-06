<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'country', 'currency', 'sort_by', 'sort_order', 'per_page']);
        $students = $this->studentService->getAll($filters);

        return response()->json($students);
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        try {
            $student = $this->studentService->create($request->validated());
            return response()->json($student, 201);
        } catch (\Exception $e) {
            Log::error('Error creating student: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create student'], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $student = $this->studentService->getById($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json($student);
    }

    public function update(UpdateStudentRequest $request, string $id): JsonResponse
    {
        $student = $this->studentService->getById($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        try {
            $student = $this->studentService->update($student, $request->validated());
            return response()->json($student);
        } catch (\Exception $e) {
            Log::error('Error updating student: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update student'], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $student = $this->studentService->getById($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $this->studentService->delete($student);

        return response()->json(['message' => 'Student deleted successfully']);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $deleted = $this->studentService->bulkDelete($request->ids);

        return response()->json(['message' => "{$deleted} students deleted successfully"]);
    }

    public function export(Request $request): JsonResponse
    {
        $filters = $request->only(['country', 'currency']);
        $students = $this->studentService->export($filters);

        return response()->json($students);
    }
}
