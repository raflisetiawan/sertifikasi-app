<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class QuizStartResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'success' => true,
            'data' => [
                'title' => $this->title,
                'description' => $this->description,
                'time_limit_minutes' => $this->time_limit_minutes,
                'passing_score' => $this->passing_score,
                'attempts_left' => $this->attempts_left,
                'questions' => collect($this->questions)->map(function ($question) {
                    // Remove correct answer and explanation from each question
                    return Arr::except($question, ['correct_answer', 'correct_answers', 'explanation']);
                })->values()->all()
            ]
        ];
    }
}
