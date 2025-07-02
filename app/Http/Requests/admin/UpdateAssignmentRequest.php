<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
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
            'instructions' => 'sometimes|string',
            'submission_requirements' => 'sometimes|array',
            'due_date' => 'sometimes|date|after:now',
            'max_file_size_mb' => 'sometimes|integer|min:1|max:100',
            'allowed_file_types' => 'sometimes|string'
        ];
    }
}