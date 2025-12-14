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
        // Helper function to safely drop foreign key
        $dropForeignKey = function ($tableName, $constraintName) {
            try {
                DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`");
            } catch (\Exception $e) {
                // Foreign key doesn't exist, ignore
            }
        };

        // Drop foreign key constraints from timetable_events if table exists
        if (Schema::hasTable('timetable_events')) {
            $dropForeignKey('timetable_events', 'timetable_events_timetable_id_foreign');
            $dropForeignKey('timetable_events', 'timetable_events_teacher_id_foreign');
        }

        // Drop foreign key constraints from timetables if table exists
        if (Schema::hasTable('timetables')) {
            if (Schema::hasColumn('timetables', 'calendar_student_id')) {
                $dropForeignKey('timetables', 'timetables_calendar_student_id_foreign');
            }
            $dropForeignKey('timetables', 'timetables_student_id_foreign');
            $dropForeignKey('timetables', 'timetables_teacher_id_foreign');
            $dropForeignKey('timetables', 'timetables_created_by_foreign');
        }

        // Drop tables
        Schema::dropIfExists('timetable_events');
        Schema::dropIfExists('timetables');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate timetables table
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('calendar_student_id')->nullable()->constrained('calendar_students')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('course_name');
            $table->string('timezone');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days_of_week');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['student_id', 'start_date']);
            $table->index(['teacher_id', 'start_date']);
        });

        // Recreate timetable_events table
        Schema::create('timetable_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->onDelete('cascade');
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('course_name');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['event_date', 'timetable_id']);
            $table->index(['teacher_id', 'event_date']);
        });
    }
};
