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
        // Modify the enum column to include 'present'
        DB::statement("ALTER TABLE `lessons` MODIFY COLUMN `status` ENUM('planned', 'present', 'completed', 'missed', 'cancelled') DEFAULT 'planned'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE `lessons` MODIFY COLUMN `status` ENUM('planned', 'completed', 'missed', 'cancelled') DEFAULT 'planned'");
    }
};
