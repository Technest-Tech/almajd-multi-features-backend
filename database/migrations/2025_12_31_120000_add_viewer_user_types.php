<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include new user types
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('admin', 'teacher', 'student', 'calendar_viewer', 'certificate_viewer') NOT NULL DEFAULT 'student'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new user types
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student'");
    }
};

