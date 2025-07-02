<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePracticeRequest extends FormRequest
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
            'time_limit_minutes' => 'nullable|integer|min:1',
            'questions' => 'sometimes|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,short_answer,true_false',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array',
            'questions.*.answer_key' => 'required',
            'questions.*.explanation' => 'nullable|string',
        ];
    }
}