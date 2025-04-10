<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseBenefitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pastikan ada pengecekan role di middleware
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'earn_by' => 'nullable|string|max:255',
        ];
    }
}
