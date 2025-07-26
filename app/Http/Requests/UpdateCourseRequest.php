<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
        $courseId = $this->route('course')->id;

        return [
            'name' => 'sometimes|required|string|max:255|unique:courses,name,' . $courseId,
            'description' => 'sometimes|required|string|min:12',
            'key_concepts' => 'sometimes|required|array',
            'key_concepts.*' => 'string|max:255',
            'facility' => 'sometimes|required|array',
            'facility.*' => 'string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'place' => 'sometimes|required|string|in:online,offline,hybrid,Online,Offline,Hybrid',
            'duration' => 'sometimes|required|string|max:12',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'operational_start' => [
                'sometimes',
                'required',
                'date',
                'after_or_equal:today'
            ],
            'operational_end' => [
                'sometimes',
                'required',
                'date',
                'after:operational_start'
            ],
            'syllabus' => 'nullable|file|mimes:pdf|max:10240',
            'status' => 'sometimes|required|in:not_started,ongoing,completed'
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
            'name.unique' => 'Nama kelas sudah digunakan',
            'description.min' => 'Deskripsi minimal 50 karakter',
            'key_concepts.array' => 'Format konsep kunci tidak valid',
            'facility.array' => 'Format fasilitas tidak valid',
            'price.numeric' => 'Harga harus berupa angka',
            'price.min' => 'Harga tidak boleh negatif',
            'place.in' => 'Tempat harus online, offline, atau hybrid',
            'image.image' => 'File harus berupa gambar',
            'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp',
            'image.max' => 'Ukuran gambar maksimal 2MB',
            'operational_start.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini',
            'operational_end.after' => 'Tanggal selesai harus setelah tanggal mulai',

            'syllabus.mimes' => 'Format silabus harus PDF',
            'syllabus.max' => 'Ukuran silabus maksimal 10MB',
            'status.in' => 'Status tidak valid'
        ];
    }
}
