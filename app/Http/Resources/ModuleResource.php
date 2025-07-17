<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'order' => $this->order,
            'type' => $this->type,
            'estimated_time_min' => $this->estimated_time_min,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail_url,
            'is_access_restricted' => $this->is_access_restricted,
            'access_start_at' => optional($this->access_start_at)->format('Y-m-d H:i:s'),
            'access_end_at' => optional($this->access_end_at)->format('Y-m-d H:i:s'),
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
