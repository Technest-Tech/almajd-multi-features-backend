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
        // Convert any 'planned' lessons to 'present' (since they should be billable)
        DB::table('lessons')
            ->where('status', 'planned')
            ->update(['status' => 'present']);

        // Update enum to only have 'present' and 'cancelled', default to 'present'
        DB::statement("ALTER TABLE `lessons` MODIFY COLUMN `status` ENUM('present', 'cancelled') DEFAULT 'present'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to include 'planned'
        DB::statement("ALTER TABLE `lessons` MODIFY COLUMN `status` ENUM('planned', 'present', 'cancelled') DEFAULT 'planned'");
    }
};
