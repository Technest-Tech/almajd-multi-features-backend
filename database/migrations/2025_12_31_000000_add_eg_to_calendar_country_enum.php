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
        // For MySQL, we need to alter the enum type
        DB::statement("ALTER TABLE calendar_teacher_timetables MODIFY COLUMN country ENUM('canada', 'uk', 'eg') DEFAULT 'canada'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE calendar_teacher_timetables MODIFY COLUMN country ENUM('canada', 'uk') DEFAULT 'canada'");
    }
};

