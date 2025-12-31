<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanMigratedDataSeeder extends Seeder
{
    /**
     * Clean all migrated data from the old database.
     * This prepares the database for a fresh migration.
     */
    public function run(): void
    {
        echo "\n=== CLEANING MIGRATED DATA ===\n\n";

        // Delete all teacher-student relationships
        $relationshipsDeleted = DB::table('teacher_student')->delete();
        echo "✓ Deleted {$relationshipsDeleted} teacher-student relationships\n";

        // Delete all migrated students (from old families table)
        $studentsDeleted = DB::table('users')
            ->where('user_type', 'student')
            ->where('email', 'like', 'student_%@almajd.com')
            ->delete();
        echo "✓ Deleted {$studentsDeleted} students\n";

        // Delete all migrated teachers (IDs with offset >= 10000)
        $teachersDeleted = DB::table('users')
            ->where('user_type', 'teacher')
            ->where('id', '>=', 10000)
            ->delete();
        echo "✓ Deleted {$teachersDeleted} teachers\n";

        // Delete migrated admins (IDs with offset >= 10000)
        $adminsDeleted = DB::table('users')
            ->where('user_type', 'admin')
            ->where('id', '>=', 10000)
            ->delete();
        echo "✓ Deleted {$adminsDeleted} admins\n";

        // Show remaining counts
        $remainingUsers = DB::table('users')->count();
        $remainingRelationships = DB::table('teacher_student')->count();

        echo "\n=== CLEANUP COMPLETE ===\n";
        echo "Remaining users: {$remainingUsers}\n";
        echo "Remaining relationships: {$remainingRelationships}\n";
        echo "\n✅ Database is clean and ready for fresh migration!\n\n";
        echo "To run the migration again:\n";
        echo "  php artisan db:seed --class=OldDataMigrationSeeder\n\n";
    }
}






