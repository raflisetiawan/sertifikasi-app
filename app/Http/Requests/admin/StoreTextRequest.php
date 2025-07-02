<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'format' => 'required|in:markdown,html',
            'order' => 'required|integer|min:0',
            'is_required' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'content.required' => 'Konten wajib diisi',
            'format.required' => 'Format wajib diisi',
            'format.in' => 'Format harus markdown atau html',
            'order.required' => 'Urutan wajib diisi',
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 0'
        ];
    }
}