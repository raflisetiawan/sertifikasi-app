<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Registration;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $registrations = Registration::all();

        return response()->json(['data' => $registrations], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $userId = $request->user_id;
        $courseId = $request->course_id;

        // Check if the user is already registered for the course
        $existingRegistration = Registration::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingRegistration) {
            // User is already registered for this course
            return response()->json(['message' => 'Anda sudah mendaftar kelas ini'], 422);
        }

        $registration = Registration::create([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
        ]);

        return response()->json(['data' => $registration, 'message' => 'Registration created successfully'], 201);
    }

    /**
     * Get the courses registered by a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function getUserCourses($userId)
    {
        // Retrieve the registrations for the given user ID
        $registrations = Registration::where('user_id', $userId)->get();

        if ($registrations->isEmpty()) {
            return response()->json(['message' => 'Anda belum mendaftar kelas'], 404);
        }

        // Extract the course IDs from registrations
        $courseIds = $registrations->pluck('course_id');

        // Retrieve the courses based on the course IDs
        $courses = Course::whereIn('id', $courseIds)->get();

        return response()->json(['data' => $courses], 200);
    }
}
