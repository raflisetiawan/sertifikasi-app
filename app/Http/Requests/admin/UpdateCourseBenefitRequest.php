<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseBenefitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'sometimes|exists:courses,id',
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'earn_by' => 'nullable|string|max:255',
        ];
    }
}
