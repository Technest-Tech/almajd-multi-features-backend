<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\Lesson;
use App\Services\LessonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LessonController extends Controller
{
    public function __construct(
        private LessonService $lessonService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['course_id', 'year', 'month', 'status', 'per_page']);
        $lessons = $this->lessonService->getAll($filters);

        return response()->json($lessons);
    }

    public function store(StoreLessonRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['created_by'] = $request->user()->id;
            $lesson = $this->lessonService->create($data);
            return response()->json($lesson->load(['course.student', 'course.teacher', 'createdBy']), 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating lesson: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Request data: ' . json_encode($request->all()));
            return response()->json(['error' => $e->getMessage() ?: 'Failed to create lesson'], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $lesson = $this->lessonService->getById($id);

        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }

        return response()->json($lesson);
    }

    public function update(UpdateLessonRequest $request, string $id): JsonResponse
    {
        $lesson = $this->lessonService->getById($id);

        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }

        try {
            $lesson = $this->lessonService->update($lesson, $request->validated());
            return response()->json($lesson);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating lesson: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update lesson'], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $lesson = $this->lessonService->getById($id);

        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }

        $this->lessonService->delete($lesson);

        return response()->json(['message' => 'Lesson deleted successfully']);
    }
}
