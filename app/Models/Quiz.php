<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'time_limit_minutes',
        'passing_score',
        'max_attempts',
        'questions'
    ];

    protected $casts = [
        'questions' => 'array'
    ];

    public function moduleContent()
    {
        return $this->morphOne(ModuleContent::class, 'content');
    }
}
