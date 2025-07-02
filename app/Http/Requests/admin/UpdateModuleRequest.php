<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order' => 'sometimes|integer|min:1',
            'type' => 'sometimes|in:prework,module,final',
            'estimated_time_min' => 'sometimes|integer|min:1',
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_access_restricted' => 'boolean',
            'access_start_at' => 'nullable|required_if:is_access_restricted,true|date',
            'access_end_at' => 'nullable|date|after:access_start_at'
        ];
    }

    public function messages(): array
    {
        return [
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1',
            'type.in' => 'Tipe modul tidak valid',
            'estimated_time_min.integer' => 'Estimasi waktu harus berupa angka',
            'estimated_time_min.min' => 'Estimasi waktu minimal 1 menit',
            'title.max' => 'Judul maksimal 255 karakter',
            'thumbnail.image' => 'File harus berupa gambar',
            'thumbnail.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'thumbnail.max' => 'Ukuran gambar maksimal 2MB',
            'access_start_at.required_if' => 'Start date is required when access is restricted',
            'access_end_at.after' => 'End date must be after start date'
        ];
    }
}