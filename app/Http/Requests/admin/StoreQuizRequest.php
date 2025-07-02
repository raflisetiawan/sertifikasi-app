<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
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
            'time_limit_minutes' => 'required|integer|min:1',
            'passing_score' => 'required|integer|between:0,100',
            'max_attempts' => 'required|integer|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,essay',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array|min:2',
            'questions.*.correct_answer' => 'required_unless:questions.*.type,essay',
            'questions.*.score' => 'required|integer|min:1',
            'order' => 'required|integer|min:0',
            'is_required' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul kuis wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'description.required' => 'Deskripsi wajib diisi',
            'time_limit_minutes.required' => 'Batas waktu wajib diisi',
            'time_limit_minutes.min' => 'Batas waktu minimal 1 menit',
            'passing_score.required' => 'Nilai kelulusan wajib diisi',
            'passing_score.between' => 'Nilai kelulusan harus antara 0-100',
            'max_attempts.required' => 'Jumlah percobaan wajib diisi',
            'max_attempts.min' => 'Jumlah percobaan minimal 1',
            'questions.required' => 'Pertanyaan wajib diisi',
            'questions.min' => 'Minimal harus ada 1 pertanyaan',
            'questions.*.question.required' => 'Pertanyaan wajib diisi',
            'questions.*.type.required' => 'Tipe pertanyaan wajib diisi',
            'questions.*.type.in' => 'Tipe pertanyaan tidak valid',
            'questions.*.options.required_if' => 'Pilihan jawaban wajib diisi untuk tipe pilihan ganda',
            'questions.*.options.min' => 'Minimal harus ada 2 pilihan jawaban',
            'questions.*.correct_answer.required_unless' => 'Jawaban benar wajib diisi',
            'questions.*.score.required' => 'Skor pertanyaan wajib diisi',
            'questions.*.score.min' => 'Skor minimal 1',
            'order.required' => 'Urutan wajib diisi',
            'order.min' => 'Urutan minimal 0'
        ];
    }
}