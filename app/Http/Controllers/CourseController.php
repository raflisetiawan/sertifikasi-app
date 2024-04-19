<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Opsional, jika Anda mengizinkan gambar.
            'operational_start' => 'required|date', // Perbarui nama kolom
            'operational_end' => 'required|date',
            'benefit' => 'required|string',
            'guidelines' => 'required|file|max:20000',
            'trainer_id' => 'required',
            'duration' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/courses', $image->hashName());
        $guidelines = $request->file('guidelines');
        $guidelines->storeAs('public/courses/guideline', $guidelines->hashName());

        $course = Course::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $image->hashName(),
            'price' => $request->price,
            'facility' => $request->facility,
            'trainer_id' => $request->trainer_id,
            'place' => $request->place,
            'operational_start' => $request->operational_start, // Perbarui nama kolom
            'operational_end' => $request->operational_end,
            'duration' => $request->duration,
            'benefit' => $request->benefit, // Add this line
            'guidelines' => $guidelines->hashName()
        ]);

        return new CourseResource(true, 'Data Course Berhasil Ditambahkan!', $course);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::with('trainer')->find($id);

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        return new CourseResource(true, 'Detail Data course!', $course);
    }

    /**
     * Update the specified resource in storage.
     */
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
            'operational_start' => 'required|date',
            'operational_end' => 'required|date',
            'benefit' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'guidelines' => 'file|max:20000', // Make guidelines optional
            'trainer_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        // Hapus file gambar lama jika ada pembaruan pada file gambar
        if ($request->file('image')) {
            Storage::delete('public/courses/' . basename($course->image));
        }

        // Hapus file pedoman lama jika ada pembaruan pada file pedoman
        if ($request->file('guidelines') && $course->guidelines) {
            Storage::delete('public/courses/guideline/' . basename($course->guidelines));
        }

        // Simpan file gambar baru
        $image = $request->file('image');
        if ($image) {
            $image->storeAs('public/courses', $image->hashName());
        }

        // Simpan file pedoman baru jika ada
        $guidelines = $request->file('guidelines');
        if ($guidelines) {
            $guidelines->storeAs('public/courses/guideline', $guidelines->hashName());
        }

        // Perbarui data kursus
        $course->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $image ? $image->hashName() : $course->image,
            'price' => $request->price,
            'trainer_id' => $request->trainer_id,
            'facility' => $request->facility,
            'place' => $request->place,
            'duration' => $request->duration,
            'operational_start' => $request->operational_start,
            'operational_end' => $request->operational_end,
            'benefit' => $request->benefit,
            'guidelines' => $guidelines ? $guidelines->hashName() : $course->guidelines,
        ]);

        return new CourseResource(true, 'Data Course Berhasil Diperbarui!', $course);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        // Hapus file gambar jika ada
        if ($course->image) {
            Storage::delete('public/courses/' . $course->image);
        }

        // Hapus file pedoman jika ada
        if ($course->guidelines) {
            Storage::delete('public/courses/guideline/' . $course->guidelines);
        }

        // Hapus entitas kursus
        $course->delete();

        // Return response
        return new CourseResource(true, 'Data Course Berhasil Dihapus!', null);
    }

    /**
     * Get related courses based on the given course ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function relatedCourse(string $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        $relatedCourses = Course::where('id', '<>', $course->id)
            ->where('trainer_id', '=', $course->trainer_id)->limit(4)
            ->get();

        // If no related courses found, fetch random courses
        if ($relatedCourses->isEmpty()) {
            $randomCourses = Course::inRandomOrder()->limit(4)->get();
            return new CourseResource(true, 'Random Courses', $randomCourses);
        }

        return new CourseResource(true, 'Related Courses', $relatedCourses);
    }

    public function getCourseNameById(string $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        return response()->json(['course_name' => $course->name], 200);
    }

    public function getCourseWithMaterials(string $id)
    {
        $course = Course::with(['materials' => function ($query) {
            $query->select('title', 'file', 'course_id', 'description');
        }])
            ->select('id', 'name', 'description', 'facility', 'duration', 'status')
            ->with(['zoomLink' => function ($query) {
                $query->select('link', 'course_id');
            }])
            ->find($id);

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $course], Response::HTTP_OK);
    }

    public function getCourseTableWithZoomLink()
    {
        $course = Course::select(
            'name',
            'operational_start',
            'operational_end',
            'id',
            'status',
        )->with('zoomLink')->latest()->get();

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        return response()->json(['data' => $course], 200);
    }

   /**
     * Get all courses with only id and name columns.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIdAndNameCourse()
    {
        // Mengambil semua kelas dengan kolom id dan name saja
        $courses = Course::select('id', 'name')->get();

        return response()->json(['data' => $courses], 200);
    }
}
