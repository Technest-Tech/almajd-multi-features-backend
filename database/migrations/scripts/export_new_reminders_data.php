<?php

/**
 * Export script for migrating data from new_reminders database to laravel-backend
 * 
 * Usage: php artisan tinker
 * Then: require 'database/migrations/scripts/export_new_reminders_data.php';
 * 
 * Or run directly: php database/migrations/scripts/export_new_reminders_data.php
 * (You'll need to configure database connection manually)
 */

// This script should be run from the new_reminders project
// It exports data to JSON files that can be imported into laravel-backend

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// Set the export directory
$exportDir = storage_path('app/exports/calendar_migration');
if (!File::exists($exportDir)) {
    File::makeDirectory($exportDir, 0755, true);
}

echo "Starting data export from new_reminders...\n";

try {
    // Export teachers
    echo "Exporting teachers...\n";
    $teachers = DB::table('teachers')->get();
    $teachersData = $teachers->map(function ($teacher) {
        return [
            'name' => $teacher->name,
            'whatsapp_number' => $teacher->whatsapp ?? $teacher->whatsapp_number ?? '',
        ];
    })->toArray();
    File::put($exportDir . '/teachers.json', json_encode($teachersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Exported " . count($teachersData) . " teachers\n";

    // Export teacher timetables
    echo "Exporting teacher timetables...\n";
    $timetables = DB::table('teachers_time_tables')->get();
    $timetablesData = $timetables->map(function ($timetable) {
        return [
            'teacher_id' => $timetable->teacher_id,
            'day' => $timetable->day,
            'start_time' => $timetable->start_time,
            'finish_time' => $timetable->finish_time ?? null,
            'student_name' => $timetable->student_name,
            'country' => $timetable->country ?? 'canada',
            'status' => $timetable->status ?? 'active',
            'reactive_date' => $timetable->reactive_date ?? null,
            'deleted_date' => $timetable->deleted_date ?? null,
        ];
    })->toArray();
    File::put($exportDir . '/teacher_timetables.json', json_encode($timetablesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Exported " . count($timetablesData) . " timetable entries\n";

    // Export exceptional classes
    echo "Exporting exceptional classes...\n";
    $exceptionalClasses = DB::table('exceptional_class')->get();
    $exceptionalData = $exceptionalClasses->map(function ($class) {
        return [
            'student_name' => $class->student_name,
            'date' => $class->date,
            'time' => $class->time,
            'teacher_id' => $class->teacher_id,
        ];
    })->toArray();
    File::put($exportDir . '/exceptional_classes.json', json_encode($exceptionalData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Exported " . count($exceptionalData) . " exceptional classes\n";

    // Export student stops
    echo "Exporting student stops...\n";
    $studentStops = DB::table('students_stops')->get();
    $stopsData = $studentStops->map(function ($stop) {
        return [
            'student_name' => $stop->student_name,
            'date_from' => $stop->date_from,
            'date_to' => $stop->date_to,
            'reason' => $stop->reason ?? null,
        ];
    })->toArray();
    File::put($exportDir . '/student_stops.json', json_encode($stopsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Exported " . count($stopsData) . " student stops\n";

    // Create a summary file
    $summary = [
        'export_date' => now()->toDateTimeString(),
        'teachers_count' => count($teachersData),
        'timetables_count' => count($timetablesData),
        'exceptional_classes_count' => count($exceptionalData),
        'student_stops_count' => count($stopsData),
        'files' => [
            'teachers.json',
            'teacher_timetables.json',
            'exceptional_classes.json',
            'student_stops.json',
        ],
    ];
    File::put($exportDir . '/summary.json', json_encode($summary, JSON_PRETTY_PRINT));

    echo "\nExport completed successfully!\n";
    echo "Files saved to: {$exportDir}\n";
    echo "Summary:\n";
    echo "  - Teachers: " . count($teachersData) . "\n";
    echo "  - Timetables: " . count($timetablesData) . "\n";
    echo "  - Exceptional Classes: " . count($exceptionalData) . "\n";
    echo "  - Student Stops: " . count($stopsData) . "\n";
    echo "\nNext step: Copy these files to laravel-backend/storage/app/exports/calendar_migration/\n";
    echo "Then run: php artisan migrate:import-calendar-data\n";

} catch (\Exception $e) {
    echo "Error during export: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
