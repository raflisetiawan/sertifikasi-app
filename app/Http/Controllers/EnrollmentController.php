<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Enrollment::class, 'enrollment');
    }

    /**
     * Display a listing of enrollments
     */
    public function index(Request $request)
    {
        $query = Enrollment::with(['user:id,name,email', 'course:id,name']);

        // Filter by user if not admin
        if (!Auth::user()->is_admin) {
            $query->where('user_id', Auth::id());
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $enrollments = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $enrollments
        ]);
    }

    /**
     * Store a new enrollment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|exists:registrations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if registration is verified
        $registration = Registration::findOrFail($request->registration_id);
        if (!$registration->verification) {
            return response()->json([
                'success' => false,
                'message' => 'Registration is not verified yet'
            ], 422);
        }

        // Check for existing enrollment
        $existingEnrollment = Enrollment::where([
            'user_id' => $registration->user_id,
            'course_id' => $registration->course_id
        ])->first();

        if ($existingEnrollment) {
            return response()->json([
                'success' => false,
                'message' => 'User is already enrolled in this course'
            ], 422);
        }

        // Create new enrollment
        $enrollment = Enrollment::create([
            'user_id' => $registration->user_id,
            'course_id' => $registration->course_id,
            'registration_id' => $registration->id,
            'status' => 'active',
            'started_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment created successfully',
            'data' => $enrollment
        ], 201);
    }

    /**
     * Update enrollment progress
     */
    public function updateProgress(Request $request, Enrollment $enrollment)
    {
        $validator = Validator::make($request->all(), [
            'progress_percentage' => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($enrollment->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update progress for active enrollments'
            ], 422);
        }

        $enrollment->updateProgress($request->progress_percentage);

        return response()->json([
            'success' => true,
            'message' => 'Progress updated successfully',
            'data' => $enrollment
        ]);
    }

    /**
     * Mark enrollment as completed
     */
    public function complete(Enrollment $enrollment)
    {
        if ($enrollment->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Can only complete active enrollments'
            ], 422);
        }

        $enrollment->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Course marked as completed',
            'data' => $enrollment
        ]);
    }
}
