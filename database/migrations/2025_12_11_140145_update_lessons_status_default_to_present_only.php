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
        // Update all existing lessons to 'present' status (they all should be present by default)
        DB::table('lessons')
            ->where('status', '!=', 'present')
            ->update(['status' => 'present']);

        // Update enum to only have 'present', default to 'present', and make it NOT NULL
        DB::statement("ALTER TABLE `lessons` MODIFY COLUMN `status` ENUM('present') DEFAULT 'present' NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to include 'cancelled' option
        DB::statement("ALTER TABLE `lessons` MODIFY COLUMN `status` ENUM('present', 'cancelled') DEFAULT 'present'");
    }
};
