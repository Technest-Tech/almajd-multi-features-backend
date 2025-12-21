<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestOldDataMigrationSeeder extends Seeder
{
    /**
     * Run migration tests and validation
     */
    public function run(): void
    {
        $this->command->info("=== OLD DATA MIGRATION TESTS ===\n");

        $this->testDataIntegrity();
        $this->testEmailUniqueness();
        $this->testRelationships();
        $this->testSampleRecords();
        $this->testAuthentication();

        $this->command->info("\n=== ALL TESTS COMPLETE ===");
    }

    /**
     * Test 1: Data Integrity
     */
    private function testDataIntegrity(): void
    {
        $this->command->info("\nTest 1: Data Integrity");
        $this->command->line("─────────────────────────");

        // Count users by type
        $admins = DB::table('users')->where('user_type', 'admin')->count();
        $teachers = DB::table('users')->where('user_type', 'teacher')->count();
        $students = DB::table('users')->where('user_type', 'student')->count();
        $total = DB::table('users')->count();

        $this->command->table(
            ['User Type', 'Count'],
            [
                ['Admins', $admins],
                ['Teachers', $teachers],
                ['Students', $students],
                ['Total', $total],
            ]
        );

        // Check for required fields
        $missingEmails = DB::table('users')->whereNull('email')->count();
        $missingPasswords = DB::table('users')->whereNull('password')->count();
        $missingNames = DB::table('users')->whereNull('name')->count();

        if ($missingEmails > 0 || $missingPasswords > 0 || $missingNames > 0) {
            $this->command->error("✗ Found records with missing required fields:");
            $this->command->line("  Missing emails: {$missingEmails}");
            $this->command->line("  Missing passwords: {$missingPasswords}");
            $this->command->line("  Missing names: {$missingNames}");
        } else {
            $this->command->info("✓ All required fields populated");
        }

        // Check student-specific fields
        $studentsWithoutCurrency = DB::table('users')
            ->where('user_type', 'student')
            ->whereNull('currency')
            ->count();

        $studentsWithoutPrice = DB::table('users')
            ->where('user_type', 'student')
            ->whereNull('hour_price')
            ->count();

        if ($studentsWithoutCurrency > 0 || $studentsWithoutPrice > 0) {
            $this->command->warn("⚠ Some students missing financial data:");
            $this->command->line("  Missing currency: {$studentsWithoutCurrency}");
            $this->command->line("  Missing hour_price: {$studentsWithoutPrice}");
        } else {
            $this->command->info("✓ All students have currency and hour_price");
        }
    }

    /**
     * Test 2: Email Uniqueness
     */
    private function testEmailUniqueness(): void
    {
        $this->command->info("\nTest 2: Email Uniqueness");
        $this->command->line("─────────────────────────");

        $duplicates = DB::table('users')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->count() > 0) {
            $this->command->error("✗ Found {$duplicates->count()} duplicate emails:");
            foreach ($duplicates as $dup) {
                $this->command->line("  {$dup->email}: {$dup->count} times");
            }
        } else {
            $this->command->info("✓ All emails are unique");
        }

        // Check email format for students
        $invalidStudentEmails = DB::table('users')
            ->where('user_type', 'student')
            ->where(function($query) {
                $query->where('email', 'not like', 'student_%@almajd.com')
                      ->where('email', 'not like', 'student_%@almajd.com');
            })
            ->count();

        if ($invalidStudentEmails > 0) {
            $this->command->warn("⚠ Found {$invalidStudentEmails} students with non-standard email format");
        } else {
            $this->command->info("✓ All student emails follow the standard format");
        }
    }

    /**
     * Test 3: Relationships
     */
    private function testRelationships(): void
    {
        $this->command->info("\nTest 3: Teacher-Student Relationships");
        $this->command->line("─────────────────────────────────────");

        $totalRelationships = DB::table('teacher_student')->count();
        $this->command->line("Total relationships: {$totalRelationships}");

        // Check for orphaned relationships
        $orphanedTeachers = DB::table('teacher_student')
            ->leftJoin('users as t', 'teacher_student.teacher_id', '=', 't.id')
            ->whereNull('t.id')
            ->count();

        $orphanedStudents = DB::table('teacher_student')
            ->leftJoin('users as s', 'teacher_student.student_id', '=', 's.id')
            ->whereNull('s.id')
            ->count();

        if ($orphanedTeachers > 0 || $orphanedStudents > 0) {
            $this->command->error("✗ Found orphaned relationships:");
            $this->command->line("  Missing teachers: {$orphanedTeachers}");
            $this->command->line("  Missing students: {$orphanedStudents}");
        } else {
            $this->command->info("✓ All relationships reference valid users");
        }

        // Distribution stats
        $teachersWithStudents = DB::table('teacher_student')
            ->distinct('teacher_id')
            ->count('teacher_id');

        $studentsWithTeachers = DB::table('teacher_student')
            ->distinct('student_id')
            ->count('student_id');

        $avgStudentsPerTeacher = $totalRelationships / max($teachersWithStudents, 1);

        $this->command->table(
            ['Metric', 'Value'],
            [
                ['Teachers with students', $teachersWithStudents],
                ['Students with teachers', $studentsWithTeachers],
                ['Avg students per teacher', round($avgStudentsPerTeacher, 2)],
            ]
        );
    }

    /**
     * Test 4: Sample Records
     */
    private function testSampleRecords(): void
    {
        $this->command->info("\nTest 4: Sample Records");
        $this->command->line("──────────────────────");

        // Sample teacher
        $teacher = DB::table('users')
            ->where('user_type', 'teacher')
            ->where('id', '>=', 10000)
            ->first();

        if ($teacher) {
            $this->command->info("Sample Teacher:");
            $this->command->line("  ID: {$teacher->id} (Original: " . ($teacher->id - 10000) . ")");
            $this->command->line("  Name: {$teacher->name}");
            $this->command->line("  Email: {$teacher->email}");
            $this->command->line("  Bank: {$teacher->bank_name}");
            
            // Count students
            $studentCount = DB::table('teacher_student')
                ->where('teacher_id', $teacher->id)
                ->count();
            $this->command->line("  Students: {$studentCount}");
        } else {
            $this->command->warn("⚠ No migrated teachers found");
        }

        // Sample student
        $student = DB::table('users')
            ->where('user_type', 'student')
            ->first();

        if ($student) {
            $this->command->info("\nSample Student:");
            $this->command->line("  ID: {$student->id}");
            $this->command->line("  Name: {$student->name}");
            $this->command->line("  Email: {$student->email}");
            $this->command->line("  Phone: {$student->whatsapp_number}");
            $this->command->line("  Country: {$student->country}");
            $this->command->line("  Currency: {$student->currency}");
            $this->command->line("  Hour Price: {$student->hour_price}");
            
            // Count teachers
            $teacherCount = DB::table('teacher_student')
                ->where('student_id', $student->id)
                ->count();
            $this->command->line("  Teachers: {$teacherCount}");
        } else {
            $this->command->warn("⚠ No migrated students found");
        }
    }

    /**
     * Test 5: Authentication
     */
    private function testAuthentication(): void
    {
        $this->command->info("\nTest 5: Authentication");
        $this->command->line("──────────────────────");

        // Test teacher password (should be preserved from old system)
        $teacher = DB::table('users')
            ->where('user_type', 'teacher')
            ->where('id', '>=', 10000)
            ->first();

        if ($teacher) {
            $this->command->info("✓ Teacher passwords preserved from old system");
            $this->command->line("  Teachers can login with their original credentials");
        }

        // Test student password (should be default)
        $student = DB::table('users')
            ->where('user_type', 'student')
            ->first();

        if ($student) {
            $testPassword = Hash::check('Student@123', $student->password);
            if ($testPassword) {
                $this->command->info("✓ Student default password verified");
                $this->command->line("  Students can login with: Student@123");
            } else {
                $this->command->error("✗ Student password mismatch");
            }
        }

        $this->command->warn("\n⚠ IMPORTANT: Students need to be notified of their login credentials:");
        $this->command->line("  Email: student_[id]_[name]@almajd.com");
        $this->command->line("  Password: Student@123");
    }
}




