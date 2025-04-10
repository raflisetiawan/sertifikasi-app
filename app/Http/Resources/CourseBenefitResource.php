<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseBenefitResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'judul' => $this->title,
            'subjudul' => $this->subtitle,
            'deskripsi' => $this->description,
            'gambar' => $this->image ? asset('storage/' . $this->image) : null,
            'diperoleh_melalui' => $this->earn_by,
            'course' => [
                'id' => $this->course->id,
                'nama' => $this->course->name,
            ],
            'dibuat_pada' => $this->created_at->toDateTimeString(),
            'diperbarui_pada' => $this->updated_at->toDateTimeString(),
        ];
    }
}
