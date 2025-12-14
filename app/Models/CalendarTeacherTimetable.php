<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarTeacherTimetable extends Model
{
    use HasFactory;

    protected $table = 'calendar_teacher_timetables';

    protected $fillable = [
        'teacher_id',
        'day',
        'start_time',
        'finish_time',
        'student_name',
        'country',
        'status',
        'reactive_date',
        'deleted_date',
    ];

    protected function casts(): array
    {
        return [
            'reactive_date' => 'date',
            'deleted_date' => 'date',
        ];
    }

    /**
     * Get the teacher that owns this timetable entry
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(CalendarTeacher::class, 'teacher_id');
    }

    /**
     * Format start_time accessor
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time ? date('g:i A', strtotime($this->start_time)) : '';
    }

    /**
     * Format finish_time accessor
     */
    public function getFormattedFinishTimeAttribute(): string
    {
        return $this->finish_time ? date('g:i A', strtotime($this->finish_time)) : '';
    }
}
