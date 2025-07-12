<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrainerCourseController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isTrainer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $trainer = Trainer::where('user_id', $user->id)->with('courses')->first();

        if (!$trainer) {
            return response()->json([]);
        }

        $courses = $trainer->courses;

        return response()->json($courses);
    }

    public function showModules(Course $course)
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

        $modules = $course->modules()->orderBy('order')->get();

        return response()->json($modules);
    }
}
