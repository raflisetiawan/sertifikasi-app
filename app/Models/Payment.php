<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'midtrans_order_id',
        'transaction_id',
        'payment_type',
        'transaction_time',
        'gross_amount',
        'transaction_status',
        'fraud_status',
        'snap_token',
        'payment_url',
        'payment_details'
    ];

    protected $casts = [
        'transaction_time' => 'datetime',
        'gross_amount' => 'decimal:2',
        'payment_details' => 'array'
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->transaction_status, ['settlement', 'capture']);
    }
}
