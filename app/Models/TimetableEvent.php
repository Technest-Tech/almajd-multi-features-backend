<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'timetable_id',
        'event_date',
        'start_time',
        'end_time',
        'teacher_id',
        'course_name',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'status' => 'string',
        ];
    }

    /**
     * Get the timetable that owns this event
     */
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    /**
     * Get the teacher assigned to this event
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get formatted start time
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time ?? '';
    }

    /**
     * Get formatted end time
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time ?? '';
    }
}
