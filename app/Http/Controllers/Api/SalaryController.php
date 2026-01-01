<?php

namespace App\Http\Controllers\Api;

use App\Exports\SalariesExport;
use App\Http\Controllers\Controller;
use App\Services\SalaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalaryController extends Controller
{
    public function __construct(
        private SalaryService $salaryService
    ) {}

    /**
     * Get teacher salaries for a specific year and month
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));

            // Validate year and month
            if (!is_numeric($year) || $year < 2000 || $year > 2100) {
                return response()->json(['error' => 'Invalid year'], 400);
            }

            if (!is_numeric($month) || $month < 1 || $month > 12) {
                return response()->json(['error' => 'Invalid month'], 400);
            }

            // Get unified hour price if provided
            $unifiedHourPrice = $request->input('unified_hour_price');
            $unifiedHourPriceFloat = null;
            if ($unifiedHourPrice !== null && is_numeric($unifiedHourPrice)) {
                $unifiedHourPriceFloat = (float) $unifiedHourPrice;
            }

            $result = $this->salaryService->getTeacherSalaries((int) $year, (int) $month, $unifiedHourPriceFloat);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching salaries: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch salaries'], 500);
        }
    }

    /**
     * Export salaries to Excel
     */
    public function export(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));

            // Validate year and month
            if (!is_numeric($year) || $year < 2000 || $year > 2100) {
                return response()->json(['error' => 'Invalid year'], 400);
            }

            if (!is_numeric($month) || $month < 1 || $month > 12) {
                return response()->json(['error' => 'Invalid month'], 400);
            }

            $unifiedHourPrice = $request->input('unified_hour_price');
            $unifiedHourPriceFloat = null;
            if ($unifiedHourPrice !== null && is_numeric($unifiedHourPrice)) {
                $unifiedHourPriceFloat = (float) $unifiedHourPrice;
            }

            $result = $this->salaryService->getTeacherSalaries((int) $year, (int) $month, $unifiedHourPriceFloat);

            // Check if there are any salaries to export
            if (empty($result['salaries'])) {
                return response()->json([
                    'error' => 'No salaries found for the selected month',
                    'message' => 'لا توجد رواتب للتصدير في هذا الشهر'
                ], 404);
            }

            $fileName = 'salaries_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.xlsx';

            $export = new SalariesExport(
                $result['salaries'],
                $result['totals_by_currency'] ?? [],
                (int) $year,
                (int) $month
            );

            return Excel::download($export, $fileName);
        } catch (\Exception $e) {
            Log::error('Error exporting salaries: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to export salaries: ' . $e->getMessage()], 500);
        }
    }
}
