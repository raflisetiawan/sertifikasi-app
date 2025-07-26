<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'verification',
        'verified_at',
        'verified_by',
        'status'
    ];

    protected $casts = [
        'verification' => 'boolean',
        'verified_at' => 'datetime'
    ];

    protected $attributes = [
        'verification' => false,
        'status' => 'pending' // pending, active, cancelled
    ];

    /**
     * Get the user that made the registration
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course being registered for
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the admin who verified the registration
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the payment associated with this registration
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the enrollment associated with this registration
     */
    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class);
    }

    /**
     * Check if registration is verified and paid, or verified for free course
     */
    public function isVerifiedAndPaid(): bool
    {
        // Jika kursus gratis (price 0), cukup verifikasi saja
        if ($this->course && $this->course->price == 0) {
            return $this->verification;
        }
        // Kursus berbayar: harus verifikasi dan payment settlement
        return $this->verification &&
               $this->payment &&
               $this->payment->transaction_status === 'settlement';
    }

    /**
     * Check if registration can be enrolled
     */
    public function canBeEnrolled(): bool
    {
        return $this->isVerifiedAndPaid() &&
               !$this->enrollment &&
               $this->course->status === 'active';
    }
}
