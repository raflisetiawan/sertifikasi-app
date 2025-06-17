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
        'thumbnail',
        'is_access_restricted',
        'access_start_at',
        'access_end_at'
    ];

    protected $appends = ['thumbnail_url'];

    protected $casts = [
        'is_access_restricted' => 'boolean',
        'access_start_at' => 'datetime',
        'access_end_at' => 'datetime'
    ];

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

    public function isAccessibleNow(): bool
    {
        if (!$this->is_access_restricted) {
            return true;
        }

        $now = now();

        return ($this->access_start_at === null || $now->gte($this->access_start_at)) &&
            ($this->access_end_at === null || $now->lte($this->access_end_at));
    }

    public static function getValidationRules(): array
    {
        return [
            'is_access_restricted' => 'boolean',
            'access_start_at' => 'nullable|date',
            'access_end_at' => 'nullable|date|after:access_start_at',
        ];
    }
}
