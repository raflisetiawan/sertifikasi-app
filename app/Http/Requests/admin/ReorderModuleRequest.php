<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'modules.required' => 'Data modul wajib diisi',
            'modules.array' => 'Format data modul tidak valid',
            'modules.*.id.required' => 'ID modul wajib diisi',
            'modules.*.id.exists' => 'Modul tidak ditemukan',
            'modules.*.order.required' => 'Urutan modul wajib diisi',
            'modules.*.order.integer' => 'Urutan harus berupa angka',
            'modules.*.order.min' => 'Urutan minimal 1'
        ];
    }
}