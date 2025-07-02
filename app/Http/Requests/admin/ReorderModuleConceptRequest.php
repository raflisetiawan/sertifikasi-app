<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderModuleConceptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'concepts' => 'required|array',
            'concepts.*.id' => 'required|exists:module_concepts,id',
            'concepts.*.order' => 'required|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'concepts.required' => 'Data konsep wajib diisi',
            'concepts.array' => 'Format data konsep tidak valid',
            'concepts.*.id.required' => 'ID konsep wajib diisi',
            'concepts.*.id.exists' => 'Konsep tidak ditemukan',
            'concepts.*.order.required' => 'Urutan konsep wajib diisi',
            'concepts.*.order.integer' => 'Urutan harus berupa angka',
            'concepts.*.order.min' => 'Urutan minimal 1'
        ];
    }
}