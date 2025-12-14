<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualBilling extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_ids',
        'amount',
        'currency',
        'message',
        'payment_token',
        'is_paid',
        'paid_at',
        'payment_method',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'student_ids' => 'array',
            'amount' => 'decimal:2',
            'currency' => Currency::class,
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created the billing
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the students for this billing
     */
    public function students()
    {
        $studentIds = $this->student_ids ?? [];
        if (empty($studentIds)) {
            return User::whereRaw('1 = 0'); // Return empty query
        }
        return User::whereIn('id', $studentIds);
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
