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
            'allowed_file_types' => 'sometimes|string',
            'order' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $moduleContentId = $this->route('id');

                    $moduleContent = \App\Models\ModuleContent::find($moduleContentId);

                    if ($moduleContent) {
                        $moduleId = $moduleContent->module_id;

                        $exists = \App\Models\ModuleContent::where('module_id', $moduleId)
                            ->where('order', $value)
                            ->where('id', '!=', $moduleContentId)
                            ->exists();

                        if ($exists) {
                            $fail('Urutan ini sudah digunakan oleh konten lain dalam modul ini.');
                        }
                    }
                },
            ],
            'is_required' => 'sometimes|boolean',
        ];
    }
}