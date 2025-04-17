<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDashboardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'image' => $this->image,
            ],
            'active_courses' => $this->whenLoaded('enrollments', function() {
                return $this->enrollments
                    ->where('status', 'active')
                    ->map(function ($enrollment) {
                        return [
                            'id' => $enrollment->id,
                            'course' => [
                                'id' => $enrollment->course->id,
                                'name' => $enrollment->course->name,
                                'description' => $enrollment->course->description,
                                'start_date' => $enrollment->course->operational_start,
                                'status' => $enrollment->course->status,
                                'image' => $enrollment->course->image,
                                'place' => $enrollment->course->place,
                            ],
                            'progress' => [
                                'percentage' => $enrollment->progress_percentage,
                                'started_at' => $enrollment->started_at,
                                'last_activity' => optional($enrollment->moduleProgresses->first())->updated_at,
                                'status' => $enrollment->status
                            ]
                        ];
                    });
            }),
            'registration_history' => $this->whenLoaded('registrations', function() {
                return $this->registrations->map(function ($registration) {
                    return [
                        'id' => $registration->id,
                        'course' => [
                            'id' => $registration->course->id,
                            'name' => $registration->course->name,
                            'price' => $registration->course->price,
                        ],
                        'status' => $registration->status,
                        'created_at' => $registration->created_at,
                        'payment' => $registration->payment ? [
                            'status' => $registration->payment->transaction_status,
                            'type' => $registration->payment->payment_type,
                            'amount' => $registration->payment->gross_amount,
                            'date' => $registration->payment->transaction_time,
                        ] : null
                    ];
                });
            })
        ];
    }
}
