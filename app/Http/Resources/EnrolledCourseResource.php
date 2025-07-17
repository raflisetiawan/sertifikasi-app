<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrolledCourseResource extends JsonResource
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
            'course' => [
                'id' => $this->course->id,
                'name' => $this->course->name,
                'image' => $this->course->image,
                'start_date' => $this->course->operational_start
            ],
            'payment_status' => $this->payment?->transaction_status ?? 'pending',
            'verification' => $this->verification,
            'progress' => $this->when($this->enrollment, function () {
                return [
                    'percentage' => $this->enrollment->progress_percentage,
                    'started_at' => $this->enrollment->started_at,
                    'completed_at' => $this->enrollment->completed_at,
                    'status' => $this->enrollment->status
                ];
            }),
            'registered_at' => $this->created_at
        ];
    }
}
