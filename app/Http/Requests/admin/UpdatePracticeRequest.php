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