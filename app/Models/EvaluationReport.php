<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_subject',
        'exam_date',
        'student_level',
        'teacher_evaluation',
        'is_checked',
        'checked_at',
        'checked_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'is_checked' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
