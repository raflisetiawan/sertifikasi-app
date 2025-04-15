<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'registration_id',
        'status',
        'started_at',
        'completed_at',
        'progress_percentage'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'float'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    // Helper methods
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100.0
        ]);
    }

    public function updateProgress(float $percentage)
    {
        $this->update([
            'progress_percentage' => $percentage
        ]);

        if ($percentage >= 100) {
            $this->markAsCompleted();
        }
    }
}
