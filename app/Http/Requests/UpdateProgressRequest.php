<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enrollment = $this->route('enrollment');
        return $enrollment && $enrollment->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'content_id' => 'sometimes|required|integer|exists:module_contents,id',
            'content_status' => 'sometimes|required|string|in:in_progress,completed',
            'score' => 'nullable|numeric|min:0|max:100',
            'completed' => 'sometimes|required|boolean'
        ];
    }
}