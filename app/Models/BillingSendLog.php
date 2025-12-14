<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingSendLog extends Model
{
    protected $fillable = [
        'year',
        'month',
        'batch_id',
        'billing_id',
        'student_id',
        'student_name',
        'phone_number',
        'status',
        'error_message',
        'sent_at',
        'attempt_number',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'sent_at' => 'datetime',
            'attempt_number' => 'integer',
        ];
    }

    /**
     * Get the billing
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(AutoBilling::class, 'billing_id');
    }

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
