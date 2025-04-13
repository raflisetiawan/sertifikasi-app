<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'order',
        'type',
        'estimated_time_min',
        'title',
        'subtitle',
        'description',
        'thumbnail'
    ];

    protected $appends = ['thumbnail_url'];

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return asset('storage/modules/thumbnails/' . $this->thumbnail);
        }
        return null;
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function concepts()
    {
        return $this->hasMany(ModuleConcept::class)->orderBy('order');
    }

    public function exercises()
    {
        return $this->hasMany(ModuleExercise::class)->orderBy('order');
    }

    public function contents()
    {
        return $this->hasMany(ModuleContent::class)->orderBy('order');
    }
}
