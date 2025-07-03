<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LiveSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Adjust authorization logic as needed, e.g., check if user has 'admin' role
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'meeting_link' => 'required|url|max:255',
            'meeting_password' => 'nullable|string|max:255',
        ];
    }
}
