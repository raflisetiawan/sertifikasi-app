<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
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
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240',
            'order' => 'required|integer|min:0',
            'is_required' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul file wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'file.required' => 'File wajib diunggah',
            'file.file' => 'Upload harus berupa file',
            'file.max' => 'Ukuran file maksimal 10MB',
            'order.required' => 'Urutan wajib diisi',
            'order.min' => 'Urutan minimal 0'
        ];
    }
}