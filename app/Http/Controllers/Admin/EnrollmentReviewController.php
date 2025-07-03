<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EnrollmentReviewRequest;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentReviewController extends Controller
{
    /**
     * Display a listing of enrollments that are completed or pending admin review.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Enrollment::with(['user', 'course'])
            ->whereIn('status', ['completed', 'pending_admin_review']);

        // Optional: Filter by course_id
        if ($request->has('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        // Optional: Filter by user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $enrollments = $query->latest('updated_at')->paginate(10);

        return response()->json($enrollments);
    }

    /**
     * Review and finalize an enrollment.
     *
     * @param EnrollmentReviewRequest $request
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function review(EnrollmentReviewRequest $request, Enrollment $enrollment): JsonResponse
    {
        // Ensure the enrollment is in a state to be reviewed by admin
        if ($enrollment->status !== 'pending_admin_review') {
            return response()->json([
                'message' => 'Enrollment is not pending admin review.'
            ], 400);
        }

        $finalScore = $request->input('final_score');

        $enrollment->markAsAdminReviewed($finalScore);

        return response()->json([
            'message' => 'Enrollment reviewed and marked as completed successfully.',
            'enrollment' => $enrollment
        ]);
    }
}
