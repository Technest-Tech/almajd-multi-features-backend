<?php

/**
 * Import script for migrating data from exported JSON files to laravel-backend
 * 
 * Usage: php artisan tinker
 * Then: require 'database/migrations/scripts/import_calendar_data.php';
 * 
 * Or create an artisan command: php artisan migrate:import-calendar-data
 */

use App\Models\CalendarTeacher;
use App\Models\CalendarTeacherTimetable;
use App\Models\CalendarExceptionalClass;
use App\Models\CalendarStudentStop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// Set the import directory
$importDir = storage_path('app/exports/calendar_migration');

if (!File::exists($importDir)) {
    echo "Error: Import directory not found: {$importDir}\n";
    echo "Please ensure you have exported data from new_reminders first.\n";
    exit(1);
}

echo "Starting data import to laravel-backend...\n";

try {
    DB::beginTransaction();

    // Import teachers first (required for foreign keys)
    echo "Importing teachers...\n";
    $teachersFile = $importDir . '/teachers.json';
    if (!File::exists($teachersFile)) {
        throw new \Exception("Teachers file not found: {$teachersFile}");
    }

    $teachersData = json_decode(File::get($teachersFile), true);
    $teacherIdMap = []; // Map old teacher IDs to new IDs

    // Get existing teachers from new_reminders to map IDs
    // Note: This assumes you have access to the old database or have exported with IDs
    // For now, we'll match by name and whatsapp
    
    foreach ($teachersData as $index => $teacherData) {
        // Check if teacher already exists
        $existingTeacher = CalendarTeacher::where('name', $teacherData['name'])
            ->where('whatsapp_number', $teacherData['whatsapp_number'])
            ->first();

        if ($existingTeacher) {
            echo "  Teacher '{$teacherData['name']}' already exists, skipping...\n";
            // You might want to map the old ID to existing teacher ID here
            continue;
        }

        $teacher = CalendarTeacher::create([
            'name' => $teacherData['name'],
            'whatsapp_number' => $teacherData['whatsapp_number'],
        ]);

        // If you have old teacher IDs in the export, map them
        // For now, we'll need to match by name/whatsapp in timetables
        echo "  Created teacher: {$teacher->name} (ID: {$teacher->id})\n";
    }

    echo "Imported " . CalendarTeacher::count() . " teachers\n";

    // Import teacher timetables
    echo "Importing teacher timetables...\n";
    $timetablesFile = $importDir . '/teacher_timetables.json';
    if (!File::exists($timetablesFile)) {
        throw new \Exception("Timetables file not found: {$timetablesFile}");
    }

    $timetablesData = json_decode(File::get($timetablesFile), true);
    $importedCount = 0;

    foreach ($timetablesData as $timetableData) {
        // Find teacher by matching name (if you have teacher name in export)
        // Or use the teacher_id mapping if available
        // For now, we'll try to find by the old teacher_id if it matches
        
        // If you exported with teacher names, match by name
        // Otherwise, you'll need to ensure teacher_id mapping is correct
        
        // This is a simplified version - you may need to adjust based on your export format
        $teacher = CalendarTeacher::find($timetableData['teacher_id']);
        
        if (!$teacher) {
            echo "  Warning: Teacher ID {$timetableData['teacher_id']} not found, skipping timetable entry\n";
            continue;
        }

        CalendarTeacherTimetable::create([
            'teacher_id' => $teacher->id,
            'day' => $timetableData['day'],
            'start_time' => $timetableData['start_time'],
            'finish_time' => $timetableData['finish_time'] ?? null,
            'student_name' => $timetableData['student_name'],
            'country' => $timetableData['country'] ?? 'canada',
            'status' => $timetableData['status'] ?? 'active',
            'reactive_date' => $timetableData['reactive_date'] ?? null,
            'deleted_date' => $timetableData['deleted_date'] ?? null,
        ]);

        $importedCount++;
    }

    echo "Imported {$importedCount} timetable entries\n";

    // Import exceptional classes
    echo "Importing exceptional classes...\n";
    $exceptionalFile = $importDir . '/exceptional_classes.json';
    if (!File::exists($exceptionalFile)) {
        throw new \Exception("Exceptional classes file not found: {$exceptionalFile}");
    }

    $exceptionalData = json_decode(File::get($exceptionalFile), true);
    $importedExceptionalCount = 0;

    foreach ($exceptionalData as $classData) {
        $teacher = CalendarTeacher::find($classData['teacher_id']);
        
        if (!$teacher) {
            echo "  Warning: Teacher ID {$classData['teacher_id']} not found, skipping exceptional class\n";
            continue;
        }

        CalendarExceptionalClass::create([
            'student_name' => $classData['student_name'],
            'date' => $classData['date'],
            'time' => $classData['time'],
            'teacher_id' => $teacher->id,
        ]);

        $importedExceptionalCount++;
    }

    echo "Imported {$importedExceptionalCount} exceptional classes\n";

    // Import student stops
    echo "Importing student stops...\n";
    $stopsFile = $importDir . '/student_stops.json';
    if (!File::exists($stopsFile)) {
        throw new \Exception("Student stops file not found: {$stopsFile}");
    }

    $stopsData = json_decode(File::get($stopsFile), true);
    $importedStopsCount = 0;

    foreach ($stopsData as $stopData) {
        CalendarStudentStop::create([
            'student_name' => $stopData['student_name'],
            'date_from' => $stopData['date_from'],
            'date_to' => $stopData['date_to'],
            'reason' => $stopData['reason'] ?? null,
        ]);

        $importedStopsCount++;
    }

    echo "Imported {$importedStopsCount} student stops\n";

    DB::commit();

    echo "\nImport completed successfully!\n";
    echo "Summary:\n";
    echo "  - Teachers: " . CalendarTeacher::count() . "\n";
    echo "  - Timetables: " . CalendarTeacherTimetable::count() . "\n";
    echo "  - Exceptional Classes: " . CalendarExceptionalClass::count() . "\n";
    echo "  - Student Stops: " . CalendarStudentStop::count() . "\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error during import: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    Log::error('Calendar data import failed: ' . $e->getMessage());
    exit(1);
}
