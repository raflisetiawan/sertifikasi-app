<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleConceptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'order' => 'sometimes|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Judul maksimal 255 karakter',
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1'
        ];
    }
}