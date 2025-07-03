<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveSessionResource extends JsonResource
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
            'course_name' => $this->whenLoaded('course', function () {
                return $this->course->name;
            }),
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => $this->start_time->toDateTimeString(),
            'end_time' => $this->end_time->toDateTimeString(),
            'meeting_link' => $this->meeting_link,
            'meeting_password' => $this->whenNotNull($this->meeting_password),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
