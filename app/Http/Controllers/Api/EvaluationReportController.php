<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvaluationReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationReportController extends Controller
{
    /**
     * List evaluation reports for the admin review page.
     * Optionally filter by creation month/year.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'is_checked' => 'nullable|boolean',
            'search' => 'nullable|string|max:255',
        ]);

        $query = EvaluationReport::query()
            ->with(['student:id,name', 'creator:id,name', 'checker:id,name'])
            ->orderByDesc('created_at');

        if (!empty($validated['year'])) {
            $query->whereYear('created_at', $validated['year']);
        }

        if (!empty($validated['month'])) {
            $query->whereMonth('created_at', $validated['month']);
        }

        if (array_key_exists('is_checked', $validated) && $validated['is_checked'] !== null) {
            $query->where('is_checked', (bool) $validated['is_checked']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('exam_subject', 'like', "%{$search}%")
                    ->orWhere('student_level', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return response()->json([
            'data' => $query->get()->map(fn (EvaluationReport $report) => $this->transform($report)),
        ]);
    }

    /**
     * Create a new evaluation report (filled by the support / certificate account).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
            'exam_subject' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'student_level' => 'required|string|max:255',
            'teacher_evaluation' => 'required|string|max:5000',
        ]);

        $validated['created_by'] = $request->user()->id;

        $report = EvaluationReport::create($validated);
        $report->load(['student:id,name', 'creator:id,name']);

        return response()->json([
            'data' => $this->transform($report),
            'message' => 'تم إنشاء التقرير بنجاح',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $report = EvaluationReport::with(['student:id,name', 'creator:id,name', 'checker:id,name'])
            ->findOrFail($id);

        return response()->json([
            'data' => $this->transform($report),
        ]);
    }

    /**
     * Mark a report as checked (admin clicks "Done").
     * Moves the report from the "not yet" section to the "checked" section.
     */
    public function markChecked(Request $request, string $id): JsonResponse
    {
        $report = EvaluationReport::findOrFail($id);

        $report->update([
            'is_checked' => true,
            'checked_at' => now(),
            'checked_by' => $request->user()->id,
        ]);

        $report->load(['student:id,name', 'creator:id,name', 'checker:id,name']);

        return response()->json([
            'data' => $this->transform($report),
            'message' => 'تم تدقيق التقرير',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        EvaluationReport::findOrFail($id)->delete();

        return response()->json([
            'message' => 'تم حذف التقرير',
        ]);
    }

    private function transform(EvaluationReport $report): array
    {
        return [
            'id' => $report->id,
            'student_id' => $report->student_id,
            'student_name' => $report->student?->name,
            'exam_subject' => $report->exam_subject,
            'exam_date' => $report->exam_date?->format('Y-m-d'),
            'student_level' => $report->student_level,
            'teacher_evaluation' => $report->teacher_evaluation,
            'is_checked' => (bool) $report->is_checked,
            'checked_at' => $report->checked_at?->toIso8601String(),
            'checked_by' => $report->checked_by,
            'checked_by_name' => $report->checker?->name,
            'created_by' => $report->created_by,
            'created_by_name' => $report->creator?->name,
            'created_at' => $report->created_at?->toIso8601String(),
            'updated_at' => $report->updated_at?->toIso8601String(),
        ];
    }
}
