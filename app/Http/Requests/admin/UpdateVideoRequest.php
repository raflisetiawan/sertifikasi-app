<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'sometimes|url',
            'provider' => 'sometimes|in:youtube,vimeo',
            'video_id' => 'sometimes|string',
            'duration_seconds' => 'sometimes|integer|min:1',
            'thumbnail_url' => 'nullable|url',
            'is_downloadable' => 'boolean',
            'captions' => 'nullable|array',
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