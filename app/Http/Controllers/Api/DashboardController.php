<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function stats(): JsonResponse
    {
        $stats = $this->dashboardService->getAdminStats();
        return response()->json($stats);
    }

    public function teacherStats(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;
        $stats = $this->dashboardService->getTeacherStats($teacherId);
        return response()->json($stats);
    }
}
