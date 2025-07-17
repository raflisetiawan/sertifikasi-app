<?php

namespace App\Http\Controllers;

use App\Http\Resources\EnrolledCourseDetailResource;
use App\Http\Resources\EnrolledCourseResource;
use App\Services\CourseEnrollmentService;
use Illuminate\Http\Request;

class CourseEnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(CourseEnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Get all courses enrolled by authenticated user
     */
    public function index(Request $request)
    {
        $registrations = $this->enrollmentService->getEnrolledCourses($request);

        return EnrolledCourseResource::collection($registrations);
    }

    /**
     * Get detailed information about specific enrolled course
     */
    public function show($id)
    {
        $registration = $this->enrollmentService->getEnrolledCourseDetails($id);

        // Verify user has access
        if (!$registration->isVerifiedAndPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this course'
            ], 403);
        }

        return new EnrolledCourseDetailResource($registration);
    }
}

