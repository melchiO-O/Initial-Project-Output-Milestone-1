<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'rental_id',
        'user_id',
        'down_payment',
        'remaining_balance',
        'damage_fee',
        'damage_notes',
        'overdue_fee',
        'final_amount_paid',
        'payment_status',
        'down_payment_paid_at',
        'full_payment_paid_at',
        'payment_method',
    ];

    protected $casts = [
        'down_payment' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'damage_fee' => 'decimal:2',
        'overdue_fee' => 'decimal:2',
        'final_amount_paid' => 'decimal:2',
        'down_payment_paid_at' => 'datetime',
        'full_payment_paid_at' => 'datetime',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}