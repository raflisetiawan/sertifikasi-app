<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'time_limit_minutes' => 'sometimes|integer|min:1',
            'passing_score' => 'sometimes|integer|between:0,100',
            'max_attempts' => 'sometimes|integer|min:1',
            'questions' => 'sometimes|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,essay',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array|min:2',
            'questions.*.correct_answer' => 'required_unless:questions.*.type,essay',
            'questions.*.score' => 'required|integer|min:1'
        ];
    }
}