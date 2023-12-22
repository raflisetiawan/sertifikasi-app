<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseZoomLink extends Model
{
    use HasFactory;


    protected $fillable = [
        'link',
        'course_id',
    ];

    // Relasi dengan tabel courses
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
