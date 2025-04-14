<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'video_url',
        'provider',
        'video_id',
        'duration_seconds',
        'thumbnail_url',
        'is_downloadable',
        'captions'
    ];

    protected $casts = [
        'is_downloadable' => 'boolean',
        'captions' => 'array',
        'duration_seconds' => 'integer'
    ];

    public function moduleContent()
    {
        return $this->morphOne(ModuleContent::class, 'content');
    }
}
