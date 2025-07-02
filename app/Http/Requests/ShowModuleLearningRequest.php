<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ShowModuleLearningRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enrollment = $this->route('enrollment');
        $module = $this->route('module');

        if (!$enrollment || !$module) {
            return false;
        }

        // Check if user is enrolled
        if ($enrollment->user_id !== Auth::id()) {
            return false;
        }

        // Check if module belongs to the enrolled course
        if ($module->course_id !== $enrollment->course_id) {
            return false;
        }

        // Check module access time
        if (!$module->isAccessibleNow()) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            // No specific validation rules for showing content, authorization handles access
        ];
    }

    public function messages(): array
    {
        return [
            'authorize' => 'You are not authorized to access this module.'
        ];
    }
}