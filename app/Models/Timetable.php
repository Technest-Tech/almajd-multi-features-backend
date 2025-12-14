<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'calendar_student_id',
        'teacher_id',
        'course_name',
        'timezone',
        'start_time',
        'end_time',
        'days_of_week',
        'start_date',
        'end_date',
        'created_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the student that owns the timetable (from users table - legacy)
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the calendar student that owns the timetable (isolated from users table)
     */
    public function calendarStudent(): BelongsTo
    {
        return $this->belongsTo(CalendarStudent::class, 'calendar_student_id');
    }

    /**
     * Get the teacher assigned to the timetable
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the user who created the timetable
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all events for this timetable
     */
    public function events(): HasMany
    {
        return $this->hasMany(TimetableEvent::class);
    }
}
