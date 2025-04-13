<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'description'
    ];

    public function moduleContent()
    {
        return $this->morphOne(ModuleContent::class, 'content');
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
