<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorize if the user is an admin or has appropriate permissions
        // For now, assuming any authenticated user can update module content
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'order' => 'sometimes|required|integer|min:0',
            'is_required' => 'sometimes|required|boolean',
            'minimum_duration_seconds' => 'nullable|integer|min:0',
            'completion_rules' => 'nullable|json'
        ];
    }
}