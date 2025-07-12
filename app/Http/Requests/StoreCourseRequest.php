<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:courses,name',
            'description' => 'required|string|min:12',
            'key_concepts' => 'required|array',
            'key_concepts.*' => 'string|max:255',
            'facility' => 'required|array',
            'facility.*' => 'string|max:255',
            'price' => 'required|numeric|min:0',
            'place' => 'required|string|in:online,offline,hybrid,Online,Offline,Hybrid',
            'duration' => 'required|string|max:50',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'operational_start' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'operational_end' => [
                'required',
                'date',
                'after:operational_start'
            ],
            'benefit' => 'required|string|min:12',
            'guidelines' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
            'syllabus' => 'nullable|file|mimes:pdf|max:10240'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kelas harus diisi',
            'name.unique' => 'Nama kelas sudah digunakan',
            'description.required' => 'Deskripsi kelas harus diisi',
            'description.min' => 'Deskripsi minimal 50 karakter',
            'key_concepts.required' => 'Konsep kunci harus diisi',
            'key_concepts.array' => 'Format konsep kunci tidak valid',
            'facility.required' => 'Fasilitas harus diisi',
            'facility.array' => 'Format fasilitas tidak valid',
            'price.required' => 'Harga kelas harus diisi',
            'price.numeric' => 'Harga harus berupa angka',
            'price.min' => 'Harga tidak boleh negatif',
            'place.required' => 'Tempat pelaksanaan harus diisi',
            'place.in' => 'Tempat harus online, offline, atau hybrid',
            'duration.required' => 'Durasi kelas harus diisi',
            'image.required' => 'Gambar kelas harus diupload',
            'image.image' => 'File harus berupa gambar',
            'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp',
            'image.max' => 'Ukuran gambar maksimal 2MB',
            'operational_start.required' => 'Tanggal mulai harus diisi',
            'operational_start.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini',
            'operational_end.required' => 'Tanggal selesai harus diisi',
            'operational_end.after' => 'Tanggal selesai harus setelah tanggal mulai',
            'benefit.required' => 'Benefit kelas harus diisi',
            'benefit.min' => 'Benefit minimal 50 karakter',
            'guidelines.required' => 'Pedoman kelas harus diupload',
            'guidelines.mimes' => 'Format pedoman harus PDF',
            'guidelines.max' => 'Ukuran pedoman maksimal 10MB',

            'syllabus.required' => 'Silabus harus diupload',
            'syllabus.mimes' => 'Format silabus harus PDF',
            'syllabus.max' => 'Ukuran silabus maksimal 10MB'
        ];
    }
}
