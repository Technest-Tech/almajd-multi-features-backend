<?php

use App\Http\Controllers\Web\CertificateController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\TimetableController;
use App\Http\Controllers\Web\CalendarStudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('certificates.index');
});

// Certificate routes (fully public, no database saving)
Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
Route::get('certificates/create', [CertificateController::class, 'create'])->name('certificates.create');
Route::get('certificates/{id}', [CertificateController::class, 'show'])->name('certificates.show');
Route::match(['get', 'post'], 'certificates/{id}/download', [CertificateController::class, 'download'])->name('certificates.download');
Route::get('certificates/health-check', [CertificateController::class, 'healthCheck'])->name('certificates.health-check');

// Timetable calendar routes (public, accessible from Flutter WebView)
// IMPORTANT: More specific routes must come before parameterized routes
Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index');
Route::get('timetable/teacher', [TimetableController::class, 'teacherView'])->name('timetable.teacher.view');
Route::get('timetable/create', [TimetableController::class, 'create'])->name('timetable.create');
Route::post('timetable', [TimetableController::class, 'store'])->name('timetable.store');

// Events routes (must come before {id} routes to avoid route conflicts)
Route::get('timetable/events', [TimetableController::class, 'events'])->name('timetable.events');
Route::post('timetable/events', [TimetableController::class, 'storeEvent'])->name('timetable.events.store');
Route::delete('timetable/events/{id}', [TimetableController::class, 'destroyEvent'])->name('timetable.events.destroy');
Route::post('timetable/events/{id}/reschedule', [TimetableController::class, 'rescheduleEvent'])->name('timetable.events.reschedule');

// Settings routes
Route::post('timetable/adjust-timezone', [TimetableController::class, 'adjustTimezone'])->name('timetable.adjust-timezone');
Route::post('timetable/send-whatsapp-reminder', [TimetableController::class, 'sendWhatsAppReminder'])->name('timetable.send-whatsapp-reminder');

// Timetable CRUD routes (parameterized routes come after specific routes)
Route::get('timetable/{id}/edit', [TimetableController::class, 'edit'])->name('timetable.edit');
Route::put('timetable/{id}', [TimetableController::class, 'update'])->name('timetable.update');
Route::delete('timetable/{id}', [TimetableController::class, 'destroy'])->name('timetable.destroy');
Route::post('timetable/{id}/toggle-status', [TimetableController::class, 'toggleStatus'])->name('timetable.toggle-status');
Route::delete('timetable/{id}/events', [TimetableController::class, 'destroyAllEvents'])->name('timetable.events.destroy-all');

// Calendar Students routes
Route::get('calendar-students', [CalendarStudentController::class, 'index'])->name('calendar-students.index');
Route::post('calendar-students', [CalendarStudentController::class, 'store'])->name('calendar-students.store');
Route::put('calendar-students/{id}', [CalendarStudentController::class, 'update'])->name('calendar-students.update');
Route::delete('calendar-students/{id}', [CalendarStudentController::class, 'destroy'])->name('calendar-students.destroy');
Route::post('calendar-students/bulk-delete', [CalendarStudentController::class, 'bulkDelete'])->name('calendar-students.bulk-delete');
Route::get('calendar-students/search', [CalendarStudentController::class, 'search'])->name('calendar-students.search');

// Payment routes (public, no auth required)
Route::get('payment/{token}', [PaymentController::class, 'show'])->name('payment.show');
// Shorter payment route alias
Route::get('pay/{token}', [PaymentController::class, 'show'])->name('payment.show.short');
Route::get('payment/{token}/status', [PaymentController::class, 'checkStatus'])->name('payment.status');
Route::get('payment/{token}/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('payment/{token}/download-report', [PaymentController::class, 'downloadReport'])->name('payment.download-report');
Route::get('payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

// XPay routes
Route::get('payment/{token}/xpay/form', [PaymentController::class, 'xpayForm'])->name('payment.xpay.form');
Route::post('payment/{token}/xpay/process', [PaymentController::class, 'xpayProcess'])->name('payment.xpay.process');
Route::post('payment/xpay/callback', [PaymentController::class, 'xpayCallback'])->name('payment.xpay.callback');

// PayPal routes
Route::get('payment/{token}/paypal/success', [PaymentController::class, 'paypalSuccess'])->name('payment.paypal.success');

// AnubPay routes
Route::post('anubpay/create-payment', [PaymentController::class, 'createAnubPayPayment'])->name('payment.anubpay.create');
Route::get('anubpay/success/{month}', [PaymentController::class, 'anubPaySuccess'])->name('payment.anubpay.success');
