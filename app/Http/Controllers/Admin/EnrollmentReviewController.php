<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EnrollmentReviewRequest;
use App\Models\Enrollment;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EnrollmentReviewController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Display a listing of enrollments that are completed or pending admin review.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Enrollment::with(['user', 'course']);

        // Filter by status
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        } else {
            // Default filter if no status is specified
            $query->whereIn('status', ['completed', 'pending_admin_review']);
        }

        // Filter by course_id
        if ($request->has('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        // Filter by user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by course name
        if ($request->has('course_name')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('course_name') . '%');
            });
        }

        // Filter by user name
        if ($request->has('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('user_name') . '%');
            });
        }

        $enrollments = $query->latest('updated_at')->paginate(10);

        $enrollments->getCollection()->transform(function ($enrollment) {
            if ($enrollment->certificate_path) {
                $enrollment->certificate_url = Storage::disk('public')->url($enrollment->certificate_path);
            }
            return $enrollment;
        });

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

    /**
     * Generate certificate for a completed enrollment.
     *
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function generateCertificate(Enrollment $enrollment): JsonResponse
    {
        if ($enrollment->status !== 'completed') {
            return response()->json([
                'message' => 'Certificate can only be generated for completed enrollments.'
            ], 400);
        }

        if ($enrollment->certificate_path !== null) {
            return response()->json([
                'message' => 'Certificate already generated.',
                'certificate_url' => Storage::disk('public')->url($enrollment->certificate_path)
            ], 200);
        }

        $certificateUrl = $this->certificateService->generateCertificate($enrollment);

        if ($certificateUrl) {
            return response()->json([
                'message' => 'Certificate generated successfully.',
                'certificate_url' => $certificateUrl
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to generate certificate.'
            ], 500);
        }
    }

    /**
     * Display detailed information for a single enrollment, including progress.
     *
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function showEnrollmentDetails(Enrollment $enrollment): JsonResponse
    {
        $enrollment->load([
            'user',
            'course',
            'moduleProgresses.module',
            'contentProgresses.moduleContent'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment details loaded successfully.',
            'data' => $enrollment
        ]);
    }
}
