<?php

namespace App\Http\Resources;

use App\Services\CourseEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrolledCourseDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $enrollmentService = app(CourseEnrollmentService::class);

        return [
            'course' => [
                'id' => $this->course->id,
                'name' => $this->course->name,
                'description' => $this->course->description,
                'key_concepts' => $this->course->key_concepts,
                'facility' => $this->course->facility,
                'start_date' => $this->course->operational_start,
                'end_date' => $this->course->operational_end,
                'status' => $this->course->status
            ],
            'modules' => $this->course->modules->map(function ($module) use ($enrollmentService) {
                $progress = $this->enrollment?->moduleProgresses
                    ->where('module_id', $module->id)
                    ->first();

                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'description' => $module->description,
                    'type' => $module->type,
                    'order' => $module->order,
                    'is_locked' => $module->is_access_restricted && !$module->isAccessibleNow(),
                    'access_restriction' => [
                        'is_restricted' => $module->is_access_restricted,
                        'start_at' => $module->access_start_at,
                        'end_at' => $module->access_end_at,
                        'status' => $enrollmentService->getModuleAccessStatus($module)
                    ],
                    'progress' => $progress ? [
                        'status' => $progress->status,
                        'percentage' => $progress->progress_percentage,
                        'started_at' => $progress->started_at,
                        'completed_at' => $progress->completed_at
                    ] : null
                ];
            }),
            'enrollment' => $this->when($this->enrollment, function () {
                return [
                    'id' => $this->enrollment->id,
                    'status' => $this->enrollment->status,
                    'progress_percentage' => $this->enrollment->progress_percentage,
                    'started_at' => $this->enrollment->started_at,
                    'completed_at' => $this->enrollment->completed_at
                ];
            }),
            'payment' => [
                'status' => $this->payment?->transaction_status,
                'amount' => $this->payment?->gross_amount,
                'date' => $this->payment?->transaction_time
            ],
            'registered_at' => $this->created_at,
            'verification' => $this->verification,
            'verified_at' => $this->verified_at
        ];
    }
}
