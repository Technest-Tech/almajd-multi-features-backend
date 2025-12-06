<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TimetableController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Students routes
    Route::apiResource('students', StudentController::class);
    Route::post('/students/bulk-delete', [StudentController::class, 'bulkDelete']);
    Route::get('/students/export', [StudentController::class, 'export']);

    // Teachers routes
    Route::apiResource('teachers', TeacherController::class);
    Route::get('/teachers/{id}/students', [TeacherController::class, 'getAssignedStudents']);
    Route::post('/teachers/{id}/assign-students', [TeacherController::class, 'assignStudents']);

    // Courses routes
    Route::apiResource('courses', CourseController::class);

    // Lessons routes
    Route::apiResource('lessons', LessonController::class);

    // Dashboard routes
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/teacher-stats', [DashboardController::class, 'teacherStats']);

    // Reports routes
    Route::get('/reports', [ReportController::class, 'index']);

    // Timetables routes - specific routes must come before resource routes
    Route::get('/timetables/events', [TimetableController::class, 'getEvents']);
    Route::put('/timetables/events/{id}', [TimetableController::class, 'updateEvent']);
    Route::delete('/timetables/events/{id}', [TimetableController::class, 'deleteEvent']);
    Route::get('/timetables/export-pdf', [TimetableController::class, 'exportPdf']);
    Route::apiResource('timetables', TimetableController::class);
});

