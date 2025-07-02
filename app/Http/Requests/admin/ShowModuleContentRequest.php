<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShowModuleContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $module = $this->route('module');
        $content = $this->route('content');

        if (!$module || !$content) {
            return false;
        }

        return $content->module_id === $module->id;
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
            'authorize' => 'Content does not belong to this module.'
        ];
    }
}