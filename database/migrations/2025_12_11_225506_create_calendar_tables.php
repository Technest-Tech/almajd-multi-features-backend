<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create calendar_teachers table
        Schema::create('calendar_teachers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('whatsapp_number');
            $table->timestamps();
            
            $table->index('name');
        });

        // Create calendar_teacher_timetables table
        Schema::create('calendar_teacher_timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('calendar_teachers')->onDelete('cascade');
            $table->string('day'); // Sunday, Monday, Tuesday, etc.
            $table->time('start_time');
            $table->time('finish_time')->nullable();
            $table->string('student_name');
            $table->enum('country', ['canada', 'uk'])->default('canada');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('reactive_date')->nullable();
            $table->date('deleted_date')->nullable();
            $table->timestamps();
            
            $table->index(['teacher_id', 'day', 'status']);
            $table->index(['student_name']);
            $table->index(['day', 'status']);
        });

        // Create calendar_exceptional_classes table
        Schema::create('calendar_exceptional_classes', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->date('date');
            $table->time('time');
            $table->foreignId('teacher_id')->constrained('calendar_teachers')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['date', 'teacher_id']);
            $table->index(['student_name']);
        });

        // Create calendar_students_stops table
        Schema::create('calendar_students_stops', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->date('date_from');
            $table->date('date_to');
            $table->text('reason')->nullable();
            $table->timestamps();
            
            $table->index(['student_name']);
            $table->index(['date_from', 'date_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_students_stops');
        Schema::dropIfExists('calendar_exceptional_classes');
        Schema::dropIfExists('calendar_teacher_timetables');
        Schema::dropIfExists('calendar_teachers');
    }
};
