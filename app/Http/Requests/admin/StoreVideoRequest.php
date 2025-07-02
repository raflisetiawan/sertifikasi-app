<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
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
            'video_url' => 'required|url',
            'provider' => 'required|in:youtube,vimeo',
            'video_id' => 'required|string',
            'duration_seconds' => 'required|integer|min:1',
            'thumbnail_url' => 'nullable|url',
            'is_downloadable' => 'boolean',
            'captions' => 'nullable|array',
            'order' => 'required|integer|min:0',
            'is_required' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul video wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'video_url.required' => 'URL video wajib diisi',
            'video_url.url' => 'Format URL video tidak valid',
            'provider.required' => 'Provider video wajib diisi',
            'provider.in' => 'Provider video tidak valid',
            'video_id.required' => 'ID video wajib diisi',
            'duration_seconds.required' => 'Durasi video wajib diisi',
            'duration_seconds.min' => 'Durasi minimal 1 detik',
            'order.required' => 'Urutan wajib diisi',
            'order.min' => 'Urutan minimal 0'
        ];
    }
}