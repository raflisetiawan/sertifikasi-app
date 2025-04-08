<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'image', 'qualification', 'description', 'starred'];
    protected function getTrainerProfile(): Attribute
    {
        return Attribute::make(
            get: fn($profiles) => asset('/storage/trainers/profiles' . $profiles),
        );
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_trainer');
    }
}
