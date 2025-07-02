<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreModuleExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module_id' => 'required|exists:modules,id',
            'description' => 'required|string',
            'order' => 'required|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'description.required' => 'Deskripsi latihan wajib diisi',
            'order.required' => 'Urutan wajib diisi',
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1'
        ];
    }
}