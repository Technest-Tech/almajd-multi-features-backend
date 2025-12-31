<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Reports module coming soon',
            'data' => [],
        ]);
    }

    /**
     * Generate PDF report for a specific student
     */
    public function generateStudentReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:users,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $studentId = $request->input('student_id');
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $student = $this->reportService->getStudent($studentId);
            
            if (!$student) {
                return response()->json(['error' => 'Student not found or is not a student type'], 404);
            }

            $lessons = $this->reportService->getStudentLessons($studentId, $fromDate, $toDate);
            $totalCost = $this->reportService->calculateTotalCost($lessons);

            // Sanitize filename to avoid issues with special characters
            $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $student->name);
            $filename = 'student-report-' . $sanitizedName . '.pdf';

            $pdf = Pdf::view('reports.student_report', [
                'student' => $student,
                'lessons' => $lessons,
                'totalCost' => $totalCost,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'reportService' => $this->reportService,
            ])
            ->format('a4')
            ->name($filename)
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                    ->margins(10, 10, 10, 10, 'mm')
                    ->showBackground()
                    ->waitUntilNetworkIdle()
                    ->timeout(120);
            });

            return $pdf->download();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Report generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'student_id' => $request->input('student_id'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
            ]);
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate PDF report for multiple students
     */
    public function generateMultiStudentReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:users,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $studentIds = $request->input('student_ids');
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $students = $this->reportService->getStudents($studentIds);
            $lessons = $this->reportService->getMultipleStudentsLessons($studentIds, $fromDate, $toDate);
            $totalCost = $this->reportService->calculateTotalCost($lessons);

            $pdf = Pdf::view('reports.multi_student_report', [
                'students' => $students,
                'lessons' => $lessons,
                'totalCost' => $totalCost,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'reportService' => $this->reportService,
            ])
            ->format('a4')
            ->name('multi-student-report.pdf')
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                    ->margins(10, 10, 10, 10, 'mm')
                    ->showBackground()
                    ->waitUntilNetworkIdle()
                    ->timeout(120);
            });

            return $pdf->download();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate academy statistics PDF report
     */
    public function generateAcademyStatisticsReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $statistics = $this->reportService->getAcademyStatistics($fromDate, $toDate);

            $pdf = Pdf::view('reports.academy_statistics_report', [
                'statistics' => $statistics,
                'reportService' => $this->reportService,
            ])
            ->format('a4')
            ->name('academy-statistics-report.pdf')
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                    ->margins(10, 10, 10, 10, 'mm')
                    ->showBackground()
                    ->waitUntilNetworkIdle()
                    ->timeout(120);
            });

            return $pdf->download();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }
}
