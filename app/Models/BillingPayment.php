<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id',
        'billing_type',
        'payment_method',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'billing_type' => 'string',
            'amount' => 'decimal:2',
            'currency' => Currency::class,
            'status' => 'string',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the billing (auto or manual)
     */
    public function billing()
    {
        if ($this->billing_type === 'auto') {
            return AutoBilling::find($this->billing_id);
        }
        return ManualBilling::find($this->billing_id);
    }
}
