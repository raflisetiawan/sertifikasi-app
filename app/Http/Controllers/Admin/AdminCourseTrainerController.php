<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Trainer;
use Illuminate\Http\Request;

class AdminCourseTrainerController extends Controller
{
    public function attach(Request $request, Course $course)
    {
        $request->validate([
            'trainer_id' => 'required|exists:trainers,id',
        ]);

        $trainer = Trainer::find($request->trainer_id);

        if (!$course->trainers()->where('trainer_id', $trainer->id)->exists()) {
            $course->trainers()->attach($trainer);
            return response()->json(['message' => 'Trainer attached to course successfully.'], 200);
        }

        return response()->json(['message' => 'Trainer already attached to this course.'], 409);
    }

    public function detach(Course $course, Trainer $trainer)
    {
        if ($course->trainers()->where('trainer_id', $trainer->id)->exists()) {
            $course->trainers()->detach($trainer);
            return response()->json(['message' => 'Trainer detached from course successfully.'], 200);
        }

        return response()->json(['message' => 'Trainer not attached to this course.'], 404);
    }

    public function index(Course $course)
    {
        return response()->json($course->trainers);
    }

    public function sync(Request $request, Course $course)
    {
        $request->validate([
            'trainer_ids' => 'nullable|array',
            'trainer_ids.*' => 'exists:trainers,id',
        ]);

        $course->trainers()->sync($request->input('trainer_ids', []));

        return response()->json(['message' => 'Course trainers synced successfully.', 'trainers' => $course->trainers()->get()], 200);
    }
}
