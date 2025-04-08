<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'course_id',
        'payment_status',
        'midtrans_order_id',
        'transaction_id',
        'payment_type',
        'transaction_time',
        'gross_amount',
        'fraud_status',
        'verification'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function getPaymentProofAttribute($value)
    {
        return asset('/storage/payment_proof_images/' . $value);
    }
}
