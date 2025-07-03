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
        'progress_percentage',
        'final_score',
        'admin_reviewed_at',
        'certificate_number',
        'certificate_path'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'admin_reviewed_at' => 'datetime',
        'progress_percentage' => 'float',
        'final_score' => 'float'
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

    // Add this relationship
    public function moduleProgresses()
    {
        return $this->hasMany(ModuleProgress::class);
    }

    public function contentProgresses()
    {
        return $this->hasMany(ContentProgress::class);
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

        if ($percentage >= 100 && $this->status !== 'completed') {
            $this->update(['status' => 'pending_admin_review']);
        }
    }

    public function markAsAdminReviewed(float $finalScore = null)
    {
        $this->update([
            'status' => 'completed',
            'final_score' => $finalScore,
            'admin_reviewed_at' => now(),
            'completed_at' => now(), // Ensure completed_at is set when admin reviews
            'progress_percentage' => 100.0 // Ensure progress is 100%
        ]);
    }
}
