<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalendarStudentRequest;
use App\Http\Requests\UpdateCalendarStudentRequest;
use App\Models\CalendarStudent;
use App\Services\CalendarStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarStudentController extends Controller
{
    protected $calendarStudentService;

    public function __construct(CalendarStudentService $calendarStudentService)
    {
        $this->calendarStudentService = $calendarStudentService;
    }

    /**
     * Display calendar students management page.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'per_page', 'sort_by', 'sort_order']);
        $students = $this->calendarStudentService->getAll($filters);

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($students);
        }

        return view('timetable.index');
    }

    /**
     * Store a newly created calendar student.
     */
    public function store(StoreCalendarStudentRequest $request): JsonResponse
    {
        try {
            $student = $this->calendarStudentService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Calendar student created successfully!',
                'student' => $student
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create calendar student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified calendar student.
     */
    public function update(UpdateCalendarStudentRequest $request, int $id): JsonResponse
    {
        try {
            $student = $this->calendarStudentService->getById($id);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Calendar student not found'
                ], 404);
            }

            $student = $this->calendarStudentService->update($student, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Calendar student updated successfully!',
                'student' => $student
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update calendar student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified calendar student.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $student = $this->calendarStudentService->getById($id);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Calendar student not found'
                ], 404);
            }

            $this->calendarStudentService->delete($student);

            return response()->json([
                'success' => true,
                'message' => 'Calendar student deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete calendar student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete calendar students.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => ['required', 'array'],
                'ids.*' => ['integer', 'exists:calendar_students,id'],
            ]);

            $deletedCount = $this->calendarStudentService->bulkDelete($request->input('ids'));

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} calendar student(s)!",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete calendar students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search calendar students.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => ['required', 'string', 'min:1'],
            ]);

            $students = $this->calendarStudentService->search($request->input('q'));

            return response()->json([
                'success' => true,
                'students' => $students
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search calendar students: ' . $e->getMessage()
            ], 500);
        }
    }
}










