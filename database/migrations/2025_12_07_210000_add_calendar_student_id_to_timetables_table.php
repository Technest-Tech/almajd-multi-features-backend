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
        Schema::table('timetables', function (Blueprint $table) {
            // Add calendar_student_id column
            $table->foreignId('calendar_student_id')->nullable()->after('student_id')->constrained('calendar_students')->onDelete('cascade');
        });
        
        // Migrate existing data: Update timetables to use calendar_student_id
        // Find calendar students by matching names with users, then update timetables
        DB::statement("
            UPDATE timetables t
            INNER JOIN users u ON t.student_id = u.id
            INNER JOIN calendar_students cs ON u.name = cs.name
            SET t.calendar_student_id = cs.id
            WHERE u.user_type = 'student'
        ");
        
        // Now make student_id nullable and remove foreign key constraint
        Schema::table('timetables', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['student_id']);
            // Make it nullable
            $table->unsignedBigInteger('student_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            // Restore student_id foreign key
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            
            // Drop calendar_student_id
            $table->dropForeign(['calendar_student_id']);
            $table->dropColumn('calendar_student_id');
        });
    }
};















