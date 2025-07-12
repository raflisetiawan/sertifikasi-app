<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\ContentProgress;
use App\Models\Enrollment;
use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainerAssignmentController extends Controller
{
    public function showSubmission(Enrollment $enrollment, ModuleContent $moduleContent)
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

        // Check if the trainer is assigned to the course of this enrollment
        if (!$trainer->courses->contains($enrollment->course_id)) {
            return response()->json(['message' => 'You are not assigned to this course.'], 403);
        }

        // Check if the module content is part of the enrollment's course
        if ($moduleContent->module->course_id !== $enrollment->course_id) {
            return response()->json(['message' => 'Module content does not belong to this enrollment\'s course.'], 400);
        }

        // Ensure the module content is an assignment
        if ($moduleContent->content_type !== 'assignment') {
            return response()->json(['message' => 'This content is not an assignment.'], 400);
        }

        $contentProgress = ContentProgress::where('enrollment_id', $enrollment->id)
            ->where('module_content_id', $moduleContent->id)
            ->first();

        if (!$contentProgress) {
            return response()->json(['message' => 'Submission not found.'], 404);
        }

        return response()->json($contentProgress);
    }

    public function gradeSubmission(Request $request, Enrollment $enrollment, ModuleContent $moduleContent)
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

        // Check if the trainer is assigned to the course of this enrollment
        if (!$trainer->courses->contains($enrollment->course_id)) {
            return response()->json(['message' => 'You are not assigned to this course.'], 403);
        }

        // Check if the module content is part of the enrollment's course
        if ($moduleContent->module->course_id !== $enrollment->course_id) {
            return response()->json(['message' => 'Module content does not belong to this enrollment\'s course.'], 400);
        }

        // Ensure the module content is an assignment
        if ($moduleContent->content_type !== 'assignment') {
            return response()->json(['message' => 'This content is not an assignment.'], 400);
        }

        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $contentProgress = ContentProgress::where('enrollment_id', $enrollment->id)
            ->where('module_content_id', $moduleContent->id)
            ->first();

        if (!$contentProgress) {
            return response()->json(['message' => 'Submission not found.'], 404);
        }

        $contentProgress->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'status' => 'completed', // Mark as completed after grading
        ]);

        return response()->json(['message' => 'Assignment graded successfully.', 'data' => $contentProgress]);
    }
}
