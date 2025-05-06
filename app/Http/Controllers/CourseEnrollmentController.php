<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseEnrollmentController extends Controller
{
    /**
     * Get all courses enrolled by authenticated user
     */
    public function index(Request $request)
    {
        $query = Registration::with(['course', 'payment', 'enrollment.moduleProgresses'])
            ->where('user_id', Auth::id());

        // Sort by latest
        if ($request->sort === 'latest') {
            $query->orderBy('created_at', 'desc');
        }

        $registrations = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'success' => true,
            'data' => $registrations->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'course' => [
                        'id' => $registration->course->id,
                        'name' => $registration->course->name,
                        'image' => $registration->course->image,
                        'start_date' => $registration->course->operational_start
                    ],
                    'payment_status' => $registration->payment?->transaction_status ?? 'pending',
                    'verification' => $registration->verification,
                    'progress' => $registration->enrollment ? [
                        'percentage' => $registration->enrollment->progress_percentage,
                        'started_at' => $registration->enrollment->started_at,
                        'completed_at' => $registration->enrollment->completed_at,
                        'status' => $registration->enrollment->status
                    ] : null,
                    'registered_at' => $registration->created_at
                ];
            })
        ]);
    }

    /**
     * Get detailed information about specific enrolled course
     */
    public function show($id)
    {
        $registration = Registration::with([
            'course' => function ($query) {
                $query->with(['modules' => function ($q) {
                    $q->orderBy('order');
                }]);
            },
            'payment',
            'enrollment.moduleProgresses'
        ])->where('user_id', Auth::id())
          ->where('course_id', $id)
          ->firstOrFail();

        // Verify user has access
        if (!$registration->isVerifiedAndPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this course'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'course' => [
                    'id' => $registration->course->id,
                    'name' => $registration->course->name,
                    'description' => $registration->course->description,
                    'key_concepts' => $registration->course->key_concepts,
                    'facility' => $registration->course->facility,
                    'start_date' => $registration->course->operational_start,
                    'end_date' => $registration->course->operational_end,
                    'status' => $registration->course->status
                ],
                'modules' => $registration->course->modules->map(function ($module) use ($registration) {
                    $progress = $registration->enrollment?->moduleProgresses
                        ->where('module_id', $module->id)
                        ->first();

                    return [
                        'id' => $module->id,
                        'title' => $module->title,
                        'description' => $module->description,
                        'type' => $module->type,
                        'order' => $module->order,
                        'progress' => $progress ? [
                            'status' => $progress->status,
                            'percentage' => $progress->progress_percentage,
                            'started_at' => $progress->started_at,
                            'completed_at' => $progress->completed_at
                        ] : null
                    ];
                }),
                'enrollment' => $registration->enrollment ? [
                    'id' => $registration->enrollment->id,
                    'status' => $registration->enrollment->status,
                    'progress_percentage' => $registration->enrollment->progress_percentage,
                    'started_at' => $registration->enrollment->started_at,
                    'completed_at' => $registration->enrollment->completed_at
                ] : null,
                'payment' => [
                    'status' => $registration->payment?->transaction_status,
                    'amount' => $registration->payment?->gross_amount,
                    'date' => $registration->payment?->transaction_time
                ],
                'registered_at' => $registration->created_at,
                'verification' => $registration->verification,
                'verified_at' => $registration->verified_at
            ]
        ]);
    }
}
