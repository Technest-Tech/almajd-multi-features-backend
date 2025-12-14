<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AutoBillingController;
use App\Http\Controllers\Api\ClientCredentialsController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ManualBillingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TimetableController;
use App\Http\Controllers\Api\V1\CalendarController;
use App\Http\Controllers\Api\V1\CalendarTeacherController;
use App\Http\Controllers\Api\V1\CalendarStudentStopController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/client-credentials', [ClientCredentialsController::class, 'getCredentials']);

// Public Calendar routes (no auth required)
Route::get('/calendar/events', [CalendarController::class, 'getEvents']);
Route::get('/calendar/reminders/daily', [CalendarController::class, 'generateDailyReminder']);
Route::get('/calendar/reminders/exceptional', [CalendarController::class, 'getExceptionalReminders']);
Route::post('/calendar/teacher-timetable', [CalendarController::class, 'storeTeacherTimetable']);
Route::put('/calendar/teacher-timetable/{id}', [CalendarController::class, 'updateTeacherTimetable']);
Route::delete('/calendar/teacher-timetable/{id}', [CalendarController::class, 'deleteTeacherTimetable']);
Route::post('/calendar/exceptional-class', [CalendarController::class, 'storeExceptionalClass']);
Route::delete('/calendar/exceptional-class/{id}', [CalendarController::class, 'deleteExceptionalClass']);
Route::get('/calendar/exceptional-classes/student', [CalendarController::class, 'getStudentExceptionalClasses']);
Route::get('/calendar/teacher/{id}/whatsapp', [CalendarController::class, 'getTeacherTimetableWhatsApp']);
Route::get('/calendar/teacher/{id}/students', [CalendarController::class, 'getTeacherStudents']);
Route::put('/calendar/student/status', [CalendarController::class, 'updateStudentStatus']);
Route::get('/calendar/students/list', [CalendarController::class, 'getStudentsList']);

// Public Calendar Teachers routes
Route::apiResource('calendar-teachers', CalendarTeacherController::class);
// Alias for typo compatibility (calender-teachers -> calendar-teachers)
Route::get('/calender-teachers', [CalendarTeacherController::class, 'index']);
Route::get('/calender-teachers/{id}', [CalendarTeacherController::class, 'show']);

// Public Calendar Student Stops routes
Route::apiResource('calendar-student-stops', CalendarStudentStopController::class);

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
    Route::post('/reports/student', [ReportController::class, 'generateStudentReport']);
    Route::post('/reports/multi-student', [ReportController::class, 'generateMultiStudentReport']);
    Route::post('/reports/academy-statistics', [ReportController::class, 'generateAcademyStatisticsReport']);

    // Salaries routes
    Route::get('/salaries', [SalaryController::class, 'index']);
    Route::get('/salaries/export', [SalaryController::class, 'export']);

    // Timetables routes - specific routes must come before resource routes
    Route::get('/timetables/events', [TimetableController::class, 'getEvents']);
    Route::put('/timetables/events/{id}', [TimetableController::class, 'updateEvent']);
    Route::delete('/timetables/events/{id}', [TimetableController::class, 'deleteEvent']);
    Route::get('/timetables/export-pdf', [TimetableController::class, 'exportPdf']);
    Route::apiResource('timetables', TimetableController::class);

    // Auto Billings routes - specific routes must come before parameterized routes
    Route::get('/auto-billings', [AutoBillingController::class, 'index']);
    Route::get('/auto-billings/totals', [AutoBillingController::class, 'totals']);
    Route::post('/auto-billings/generate', [AutoBillingController::class, 'generate']);
    Route::post('/auto-billings/send-all-whatsapp', [AutoBillingController::class, 'sendAllWhatsApp']);
    Route::get('/auto-billings/send-logs', [AutoBillingController::class, 'getSendLogs']);
    Route::post('/auto-billings/resume-send-whatsapp', [AutoBillingController::class, 'resumeSendWhatsApp']);
    // Parameterized routes come after specific routes
    Route::get('/auto-billings/{id}', [AutoBillingController::class, 'show']);
    Route::post('/auto-billings/{id}/mark-paid', [AutoBillingController::class, 'markAsPaid']);
    Route::post('/auto-billings/{id}/send-whatsapp', [AutoBillingController::class, 'sendWhatsApp']);

    // Manual Billings routes
    Route::apiResource('manual-billings', ManualBillingController::class);
    Route::post('/manual-billings/{id}/mark-paid', [ManualBillingController::class, 'markAsPaid']);
    Route::post('/manual-billings/{id}/send-whatsapp', [ManualBillingController::class, 'sendWhatsApp']);

    // Payment Dashboard routes
    Route::get('/payment-dashboard/statistics', [\App\Http\Controllers\Api\PaymentDashboardController::class, 'getStatistics']);

    // Payment Settings routes
    Route::get('/payment-settings', [\App\Http\Controllers\Api\PaymentSettingsController::class, 'index']);
    Route::put('/payment-settings', [\App\Http\Controllers\Api\PaymentSettingsController::class, 'update']);
    
    // Lesson Settings routes
    Route::get('/lesson-settings', [\App\Http\Controllers\Api\PaymentSettingsController::class, 'getLessonSettings']);
    Route::put('/lesson-settings', [\App\Http\Controllers\Api\PaymentSettingsController::class, 'updateLessonSettings']);
});

// Public payment webhook routes (no auth required)
Route::post('/handle-anubpay-payment', [\App\Http\Controllers\Web\PaymentController::class, 'handleAnubPayPayment']);

