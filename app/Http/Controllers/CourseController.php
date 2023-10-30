<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::orderBy('created_at', 'desc')->get();

        // Return a collection of courses as a resource
        return new CourseResource(true, 'List Data Courses', $courses);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'facility' => 'required|string',
            'price' => 'required|numeric',
            'place' => 'required|string',
            'time' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Opsional, jika Anda mengizinkan gambar.
            'operational' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/courses', $image->hashName());

        $course = Course::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $image->hashName(),
            'price' => $request->price,
            'facility' => $request->facility,
            'place' => $request->place,
            'time' => $request->time,
            'operational' => $request->operational,
        ]);

        return new CourseResource(true, 'Data Course Berhasil Ditambahkan!', $course);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::find($id);
        return new CourseResource(true, 'Detail Data course!', $course);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'facility' => 'required|string',
            'price' => 'required|numeric',
            'place' => 'required|string',
            'time' => 'required|string', // Opsional, jika Anda mengizinkan gambar.
            'operational' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        if ($request->file('image')) {
            Storage::delete('public/courses/' . basename($course->image));
        }

        $image = $request->file('image');

        if ($image) {
            $image = $request->file('image');
            $image->storeAs('public/courses', $image->hashName());

            $course->update([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $image->hashName(),
                'price' => $request->price,
                'facility' => $request->facility,
                'place' => $request->place,
                'time' => $request->time,
                'operational' => $request->operational,
            ]);
        } else {
            $course->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'facility' => $request->facility,
                'place' => $request->place,
                'time' => $request->time,
                'operational' => $request->operational,
            ]);
        }
        return new CourseResource(true, 'Data Course Berhasil Diperbarui!', $course);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        Storage::delete('public/courses/' . $course->image);

        //delete course
        $course->delete();

        //return response
        return new CourseResource(true, 'Data Course Berhasil Dihapus!', null);
    }
}
