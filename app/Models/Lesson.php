<?php

namespace App\Models;

use App\Enums\LessonStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'date',
        'duration',
        'status',
        'notes',
        'duty',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => LessonStatus::class,
            'duty' => 'decimal:2',
        ];
    }

    /**
     * Get the course that owns the lesson
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the user who created the lesson
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

