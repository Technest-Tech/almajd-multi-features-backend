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
        // Update enum to remove 'completed' and 'missed', keep only 'planned', 'present', 'cancelled'
        DB::statement("ALTER TABLE `lessons` MODIFY COLUMN `status` ENUM('planned', 'present', 'cancelled') DEFAULT 'planned'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to include all statuses
        DB::statement("ALTER TABLE `lessons` MODIFY COLUMN `status` ENUM('planned', 'present', 'completed', 'missed', 'cancelled') DEFAULT 'planned'");
    }
};
