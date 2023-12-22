<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trainer_id',
        'description',
        'facility',
        'price',
        'place',
        'duration',
        'image',
        'operational_start',
        'operational_end',
        'benefit', // Tambah kolom ini
        'guidelines', // Tambah kolom ini
    ];



    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function zoomLink()
    {
        return $this->hasOne(CourseZoomLink::class);
    }
}
