<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseBenefit extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'subtitle',
        'description',
        'image',
        'earn_by',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
