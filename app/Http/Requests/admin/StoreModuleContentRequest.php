<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreModuleContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorize if the user is an admin or has appropriate permissions
        // For now, assuming any authenticated user can create module content
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:video,text,quiz,assignment,file,practice',
            'content_id' => 'required|integer',
            'order' => 'required|integer|min:0',
            'is_required' => 'boolean',
            'minimum_duration_seconds' => 'nullable|integer|min:0',
            'completion_rules' => 'nullable|json'
        ];
    }
}