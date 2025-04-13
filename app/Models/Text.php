<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'format'
    ];

    public function moduleContent()
    {
        return $this->morphOne(ModuleContent::class, 'content');
    }
}
