<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    public function __construct(
        private CourseService $courseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['student_id', 'teacher_id', 'search', 'per_page']);
        $courses = $this->courseService->getAll($filters);

        return response()->json($courses);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        try {
            $course = $this->courseService->create($request->validated());
            return response()->json($course->load(['student', 'teacher']), 201);
        } catch (\Exception $e) {
            Log::error('Error creating course: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create course'], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $course = $this->courseService->getById($id);

        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        return response()->json($course);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'student_id' => 'sometimes|required|exists:users,id',
            'teacher_id' => 'sometimes|required|exists:users,id',
        ]);

        $course = $this->courseService->getById($id);

        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        try {
            $course = $this->courseService->update($course, $request->only(['name', 'student_id', 'teacher_id']));
            return response()->json($course);
        } catch (\Exception $e) {
            Log::error('Error updating course: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update course'], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $course = $this->courseService->getById($id);

        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        $this->courseService->delete($course);

        return response()->json(['message' => 'Course deleted successfully']);
    }
}
