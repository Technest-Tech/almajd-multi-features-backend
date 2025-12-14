<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoBilling extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'year',
        'month',
        'total_hours',
        'total_amount',
        'currency',
        'is_paid',
        'paid_at',
        'payment_method',
        'payment_token',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'total_hours' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'currency' => Currency::class,
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the student that owns the billing
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Generate a unique payment token
     */
    public function generatePaymentToken(): string
    {
        do {
            // Generate 12-character token: "almajd" (6 chars) + 6 random hex characters
            $randomPart = bin2hex(random_bytes(3)); // 3 bytes = 6 hex characters
            $token = 'almajd' . $randomPart; // Total: 12 characters
        } while (self::where('payment_token', $token)->exists());

        $this->payment_token = $token;
        $this->save();

        return $token;
    }
}
