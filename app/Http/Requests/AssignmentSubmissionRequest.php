<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentSubmissionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'submission_file' => 'required|file|max:10240', // Max 10MB
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
