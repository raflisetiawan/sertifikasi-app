<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'sometimes|string',
            'order' => 'sometimes|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1'
        ];
    }
}