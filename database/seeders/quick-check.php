#!/usr/bin/env php
<?php

/*
|--------------------------------------------------------------------------
| Quick Migration Check Script
|--------------------------------------------------------------------------
|
| Quick verification of migrated data from old database.
|
| Usage:
|   php database/seeders/quick-check.php
|
*/

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║           MIGRATION VERIFICATION - QUICK CHECK            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Get counts
$totalUsers = DB::table('users')->count();
$admins = DB::table('users')->where('user_type', 'admin')->count();
$teachers = DB::table('users')->where('user_type', 'teacher')->count();
$students = DB::table('users')->where('user_type', 'student')->count();
$relationships = DB::table('teacher_student')->count();

echo "👥 USERS:\n";
echo "─────────────────\n";
echo "  Admins:   " . str_pad($admins, 6, ' ', STR_PAD_LEFT) . "\n";
echo "  Teachers: " . str_pad($teachers, 6, ' ', STR_PAD_LEFT) . "\n";
echo "  Students: " . str_pad($students, 6, ' ', STR_PAD_LEFT) . "\n";
echo "  ─────────────────\n";
echo "  TOTAL:    " . str_pad($totalUsers, 6, ' ', STR_PAD_LEFT) . "\n\n";

echo "🔗 RELATIONSHIPS:\n";
echo "─────────────────\n";
echo "  Total:    " . str_pad($relationships, 6, ' ', STR_PAD_LEFT) . "\n\n";

// Get ID ranges
$teacherMin = DB::table('users')->where('user_type', 'teacher')->min('id');
$teacherMax = DB::table('users')->where('user_type', 'teacher')->max('id');
$studentMin = DB::table('users')->where('user_type', 'student')->min('id');
$studentMax = DB::table('users')->where('user_type', 'student')->max('id');

echo "📊 ID RANGES:\n";
echo "─────────────────\n";
echo "  Teachers: {$teacherMin} → {$teacherMax}\n";
echo "  Students: {$studentMin} → {$studentMax}\n\n";

// Sample records
echo "📋 SAMPLE RECORDS:\n";
echo "─────────────────────────────────────────────────────────\n";

$sampleTeacher = DB::table('users')->where('user_type', 'teacher')->first();
if ($sampleTeacher) {
    $teacherStudents = DB::table('teacher_student')->where('teacher_id', $sampleTeacher->id)->count();
    echo "Teacher:\n";
    echo "  ID:       {$sampleTeacher->id}\n";
    echo "  Name:     {$sampleTeacher->name}\n";
    echo "  Email:    {$sampleTeacher->email}\n";
    echo "  Students: {$teacherStudents}\n\n";
}

$sampleStudent = DB::table('users')->where('user_type', 'student')->first();
if ($sampleStudent) {
    $studentTeachers = DB::table('teacher_student')->where('student_id', $sampleStudent->id)->count();
    echo "Student:\n";
    echo "  ID:       {$sampleStudent->id}\n";
    echo "  Name:     {$sampleStudent->name}\n";
    echo "  Email:    {$sampleStudent->email}\n";
    echo "  Phone:    {$sampleStudent->whatsapp_number}\n";
    echo "  Currency: {$sampleStudent->currency}\n";
    echo "  Teachers: {$studentTeachers}\n\n";
}

echo "✅ Migration verification complete!\n\n";
echo "For detailed tests, run:\n";
echo "  php artisan db:seed --class=TestOldDataMigrationSeeder\n\n";
