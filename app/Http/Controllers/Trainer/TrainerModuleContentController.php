<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainerModuleContentController extends Controller
{
    public function index(Module $module)
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

        // Check if the module belongs to a course assigned to this trainer
        $course = $module->course;
        if (!$course || !$trainer->courses->contains($course->id)) {
            return response()->json(['message' => 'You are not assigned to the course of this module.'], 403);
        }

        $contents = $module->moduleContents()->orderBy('order')->get();

        return response()->json($contents);
    }
}
