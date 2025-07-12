<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
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
            'description' => 'required|string',
            'instructions' => 'required|string',
            'submission_requirements' => 'required|array',
            'due_date' => 'required|date|after:now',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
            'allowed_file_types' => 'required|array',
            'order' => 'required|integer|min:0',
            'is_required' => 'required|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul tugas wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'description.required' => 'Deskripsi wajib diisi',
            'instructions.required' => 'Instruksi wajib diisi',
            'submission_requirements.required' => 'Persyaratan submission wajib diisi',
            'submission_requirements.array' => 'Format persyaratan submission tidak valid',
            'due_date.required' => 'Tanggal deadline wajib diisi',
            'due_date.date' => 'Format tanggal tidak valid',
            'due_date.after' => 'Tanggal deadline harus setelah waktu sekarang',
            'max_file_size_mb.required' => 'Ukuran file maksimal wajib diisi',
            'max_file_size_mb.integer' => 'Ukuran file harus berupa angka',
            'max_file_size_mb.min' => 'Ukuran file minimal 1MB',
            'max_file_size_mb.max' => 'Ukuran file maksimal 100MB',
            'allowed_file_types.required' => 'Tipe file yang diizinkan wajib diisi',
            'allowed_file_types.array' => 'Tipe file harus berupa array',
            'order.required' => 'Urutan wajib diisi',
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 0',
            'is_required.required' => 'Status is_required wajib diisi'
        ];
    }
}