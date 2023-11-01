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
        'description',
        'facility',
        'price',
        'place',
        'time',
        'image',
        'operational_start', // Tambahkan kolom ini
        'operational_end',
    ];

    protected function resume(): Attribute
    {
        return Attribute::make(
            get: fn ($courses) => asset('/storage/courses/' . $courses),
        );
    }
}
