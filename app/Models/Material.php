<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'file', 'course_id'];

    /**
     * Get the course that owns the material.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    protected function file(): Attribute
    {
        return Attribute::make(
            get: fn ($file) => asset('/storage/courses/materials/' . $file),
        );
    }
}
