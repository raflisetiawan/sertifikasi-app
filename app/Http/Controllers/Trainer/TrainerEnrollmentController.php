<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Enrollment;

class TrainerEnrollmentController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isTrainer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return response()->json(['message' => 'Trainer profile not found.'], 404);
        }

        // Get all course IDs that this trainer is assigned to
        $trainerCourseIds = $trainer->courses->pluck('id');

        // Get enrollments for these courses, eager load user and course details
        $enrollments = Enrollment::whereIn('course_id', $trainerCourseIds)
            ->with(['user', 'course'])
            ->get();

        return response()->json($enrollments);
    }

    public function showProgress(Enrollment $enrollment)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isTrainer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return response()->json(['message' => 'Trainer profile not found.'], 404);
        }

        // Check if the enrollment's course is assigned to this trainer
        if (!$trainer->courses->contains($enrollment->course_id)) {
            return response()->json(['message' => 'You are not assigned to the course of this enrollment.'], 403);
        }

        // Load detailed progress for the enrollment
        $enrollment->load([
            'moduleProgresses' => function ($query) {
                $query->with(['module', 'contentProgress.moduleContent']);
            },
            'contentProgresses.moduleContent',
            'user',
            'course'
        ]);

        return response()->json($enrollment);
    }

    public function getCourseEnrollments(Course $course)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isTrainer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return response()->json(['message' => 'Trainer profile not found.'], 404);
        }

        // Check if the trainer is assigned to this course
        if (!$trainer->courses->contains($course->id)) {
            return response()->json(['message' => 'You are not assigned to this course.'], 403);
        }

        $enrollments = Enrollment::where('course_id', $course->id)
            ->with(['user', 'moduleProgresses.module', 'contentProgresses.moduleContent'])
            ->get();

        return response()->json($enrollments);
    }
}
