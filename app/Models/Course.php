<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'key_concepts',
        'facility',
        'price',
        'place',
        'duration',
        'image',
        'operational_start',
        'operational_end',
        'syllabus_path'
    ];

    protected $casts = [
        'operational_start' => 'datetime',
        'operational_end' => 'datetime',
        'key_concepts' => 'array',
        'facility' => 'array',
        'status' => CourseStatus::class,
    ];

    public function trainers()
    {
        return $this->belongsToMany(Trainer::class, 'course_trainer');
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function forum()
    {
        return $this->hasOne(Forum::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class);
    }

    public function courseBenefits()
    {
        return $this->hasMany(CourseBenefit::class);
    }

    protected static function booted()
    {
        static::created(function ($course) {
            $course->forum()->create([
                'title' => 'Forum Diskusi: ' . $course->name,
                'description' => 'Selamat datang di forum diskusi untuk kursus ' . $course->name . '. Silakan mulai topik baru untuk berdiskusi.',
            ]);
        });
    }
}
