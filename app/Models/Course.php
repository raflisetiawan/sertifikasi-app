<?php

namespace App\Models;

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
        'status',
        'benefit',
        'guidelines',
        'syllabus_path',
        'certificate_example_path',
        'certificate_template_path',
        'schedule_path'
    ];

    protected $casts = [
        'operational_start' => 'datetime',
        'operational_end' => 'datetime',
        'key_concepts' => 'array',
        'facility' => 'array',
    ];

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function trainers()
    {
        return $this->belongsToMany(Trainer::class, 'course_trainer');
    }

    public function zoomLink()
    {
        return $this->hasOne(CourseZoomLink::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }
}
