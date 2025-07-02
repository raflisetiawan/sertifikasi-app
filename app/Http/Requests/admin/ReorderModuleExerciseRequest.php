<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderModuleExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exercises' => 'required|array',
            'exercises.*.id' => 'required|exists:module_exercises,id',
            'exercises.*.order' => 'required|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'exercises.required' => 'Data latihan wajib diisi',
            'exercises.array' => 'Format data latihan tidak valid',
            'exercises.*.id.required' => 'ID latihan wajib diisi',
            'exercises.*.id.exists' => 'Latihan tidak ditemukan',
            'exercises.*.order.required' => 'Urutan latihan wajib diisi',
            'exercises.*.order.integer' => 'Urutan harus berupa angka',
            'exercises.*.order.min' => 'Urutan minimal 1'
        ];
    }
}