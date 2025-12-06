<?php

use App\Http\Controllers\Web\CertificateController;
use App\Http\Controllers\Web\TimetableController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('certificates.index');
});

// Certificate routes (fully public, no database saving)
Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
Route::get('certificates/create', [CertificateController::class, 'create'])->name('certificates.create');
Route::get('certificates/{id}', [CertificateController::class, 'show'])->name('certificates.show');
Route::match(['get', 'post'], 'certificates/{id}/download', [CertificateController::class, 'download'])->name('certificates.download');

// Timetable calendar routes (public, accessible from Flutter WebView)
// IMPORTANT: More specific routes must come before parameterized routes
Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index');
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
