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
            'captions' => 'nullable|array'
        ];
    }
}