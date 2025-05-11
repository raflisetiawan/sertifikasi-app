<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Practice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'time_limit_minutes',
        'questions'
    ];

    protected $casts = [
        'questions' => 'array'
    ];

    // Make sure hidden fields don't interfere with morphing
    protected $hidden = [
        'questions.*.answer_key',
        'questions.*.explanation'
    ];

    // Add appends to ensure these fields are always available
    protected $appends = ['content_type'];

    public function moduleContent()
    {
        return $this->morphOne(ModuleContent::class, 'content');
    }

    // Add this method to ensure proper morphing
    public function getContentTypeAttribute()
    {
        return 'practice';
    }

    // Add this method for consistent morphing
    public function getMorphClass()
    {
        return 'practice';
    }

    public function getForStudent()
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'time_limit_minutes' => $this->time_limit_minutes,
            'questions' => collect($this->questions)->map(function ($question) {
                return [
                    'question' => $question['question'],
                    'type' => $question['type'],
                    'options' => $question['options'] ?? [],
                ];
            })->values()->all()
        ];
    }
}
