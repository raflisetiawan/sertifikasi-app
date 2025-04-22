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
                'name' => 'required|string|max:255|unique:courses,name',
                'description' => 'required|string|min:12',
                'key_concepts' => 'required|array',
                'key_concepts.*' => 'string|max:255',
                'facility' => 'required|array',
                'facility.*' => 'string|max:255',
                'price' => 'required|numeric|min:0',
                'place' => 'required|string|in:online,offline,hybrid,Online,Offline,Hybrid',
                'duration' => 'required|string|max:50',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'operational_start' => [
                    'required',
                    'date',
                    'after_or_equal:today'
                ],
                'operational_end' => [
                    'required',
                    'date',
                    'after:operational_start'
                ],
                'benefit' => 'required|string|min:12',
                'guidelines' => 'required|file|mimes:pdf|max:10240', // 10MB max
                'trainer_ids' => 'required|array|min:1',
                'trainer_ids.*' => 'exists:trainers,id',
                'syllabus' => 'required|file|mimes:pdf|max:10240',
                'schedule' => 'required|file|mimes:pdf|max:10240'
            ], [
                'name.required' => 'Nama kelas harus diisi',
                'name.unique' => 'Nama kelas sudah digunakan',
                'description.required' => 'Deskripsi kelas harus diisi',
                'description.min' => 'Deskripsi minimal 50 karakter',
                'key_concepts.required' => 'Konsep kunci harus diisi',
                'key_concepts.array' => 'Format konsep kunci tidak valid',
                'facility.required' => 'Fasilitas harus diisi',
                'facility.array' => 'Format fasilitas tidak valid',
                'price.required' => 'Harga kelas harus diisi',
                'price.numeric' => 'Harga harus berupa angka',
                'price.min' => 'Harga tidak boleh negatif',
                'place.required' => 'Tempat pelaksanaan harus diisi',
                'place.in' => 'Tempat harus online, offline, atau hybrid',
                'duration.required' => 'Durasi kelas harus diisi',
                'image.required' => 'Gambar kelas harus diupload',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp',
                'image.max' => 'Ukuran gambar maksimal 2MB',
                'operational_start.required' => 'Tanggal mulai harus diisi',
                'operational_start.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini',
                'operational_end.required' => 'Tanggal selesai harus diisi',
                'operational_end.after' => 'Tanggal selesai harus setelah tanggal mulai',
                'benefit.required' => 'Benefit kelas harus diisi',
                'benefit.min' => 'Benefit minimal 50 karakter',
                'guidelines.required' => 'Pedoman kelas harus diupload',
                'guidelines.mimes' => 'Format pedoman harus PDF',
                'guidelines.max' => 'Ukuran pedoman maksimal 10MB',
                'trainer_ids.required' => 'Trainer harus dipilih',
                'trainer_ids.min' => 'Minimal pilih 1 trainer',
                'trainer_ids.*.exists' => 'Trainer tidak valid',
                'syllabus.required' => 'Silabus harus diupload',
                'syllabus.mimes' => 'Format silabus harus PDF',
                'syllabus.max' => 'Ukuran silabus maksimal 10MB',
                'schedule.required' => 'Jadwal kelas harus diupload',
                'schedule.mimes' => 'Format jadwal harus PDF',
                'schedule.max' => 'Ukuran jadwal maksimal 10MB'
            ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Handle file uploads
        $imageHash = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageHash = $image->hashName();
            $image->storeAs('public/courses', $imageHash);
        }

        $guidelinesHash = null;
        if ($request->hasFile('guidelines')) {
            $guidelines = $request->file('guidelines');
            $guidelinesHash = $guidelines->hashName();
            $guidelines->storeAs('public/courses/guideline', $guidelinesHash);
        }

        $syllabusHash = null;
        if ($request->hasFile('syllabus')) {
            $syllabus = $request->file('syllabus');
            $syllabusHash = $syllabus->hashName();
            $syllabus->storeAs('public/courses/syllabus', $syllabusHash);
        }

        $scheduleHash = null;
        if ($request->hasFile('schedule')) {
            $schedule = $request->file('schedule');
            $scheduleHash = $schedule->hashName();
            $schedule->storeAs('public/courses/schedules', $scheduleHash);
        }

        // Create course
        $course = Course::create([
            'name' => $request->name,
            'description' => $request->description,
            'key_concepts' => $request->key_concepts,
            'facility' => $request->facility,
            'price' => $request->price,
            'place' => $request->place,
            'duration' => $request->duration,
            'image' => $imageHash,
            'operational_start' => $request->operational_start,
            'operational_end' => $request->operational_end,
            'status' => 'not_started',
            'benefit' => $request->benefit,
            'guidelines' => $guidelinesHash,
            'syllabus_path' => $syllabusHash,
            'schedule_path' => $scheduleHash
        ]);

        // Attach trainers
        $course->trainers()->attach($request->trainer_ids);

        return new CourseResource(true, 'Data Course Berhasil Ditambahkan!', $course);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::with('trainers')->find($id);

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        return new CourseResource(true, 'Detail Data course!', $course);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:courses,name,'.$id,
                'description' => 'sometimes|required|string|min:12',
                'key_concepts' => 'sometimes|required|array',
                'key_concepts.*' => 'string|max:255',
                'facility' => 'sometimes|required|array',
                'facility.*' => 'string|max:255',
                'price' => 'sometimes|required|numeric|min:0',
                'place' => 'sometimes|required|string|in:online,offline,hybrid,Online,Offline,Hybrid',
                'duration' => 'sometimes|required|string|max:12',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'operational_start' => [
                    'sometimes',
                    'required',
                    'date',
                    'after_or_equal:today'
                ],
                'operational_end' => [
                    'sometimes',
                    'required',
                    'date',
                    'after:operational_start'
                ],
                'benefit' => 'sometimes|required|string|min:12',
                'guidelines' => 'nullable|file|mimes:pdf|max:10240',
                'trainer_ids' => 'sometimes|required|array|min:1',
                'trainer_ids.*' => 'exists:trainers,id',
                'syllabus' => 'nullable|file|mimes:pdf|max:10240',
                'schedule' => 'nullable|file|mimes:pdf|max:10240',
                'status' => 'sometimes|required|in:not_started,ongoing,completed'
            ], [
                'name.unique' => 'Nama kelas sudah digunakan',
                'description.min' => 'Deskripsi minimal 50 karakter',
                'key_concepts.array' => 'Format konsep kunci tidak valid',
                'facility.array' => 'Format fasilitas tidak valid',
                'price.numeric' => 'Harga harus berupa angka',
                'price.min' => 'Harga tidak boleh negatif',
                'place.in' => 'Tempat harus online, offline, atau hybrid',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp',
                'image.max' => 'Ukuran gambar maksimal 2MB',
                'operational_start.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini',
                'operational_end.after' => 'Tanggal selesai harus setelah tanggal mulai',
                'benefit.min' => 'Benefit minimal 50 karakter',
                'guidelines.mimes' => 'Format pedoman harus PDF',
                'guidelines.max' => 'Ukuran pedoman maksimal 10MB',
                'trainer_ids.min' => 'Minimal pilih 1 trainer',
                'trainer_ids.*.exists' => 'Trainer tidak valid',
                'syllabus.mimes' => 'Format silabus harus PDF',
                'syllabus.max' => 'Ukuran silabus maksimal 10MB',
                'schedule.mimes' => 'Format jadwal harus PDF',
                'schedule.max' => 'Ukuran jadwal maksimal 10MB',
                'status.in' => 'Status tidak valid'
            ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        // Handle file updates
        if ($request->hasFile('image')) {
            Storage::delete('public/courses/' . $course->image);
            $image = $request->file('image');
            $imageHash = $image->hashName();
            $image->storeAs('public/courses', $imageHash);
            $course->image = $imageHash;
        }

        // Similar file handling for other files...
        // (guidelines, syllabus, certificate_example, schedule)

        $course->update([
            'name' => $request->name,
            'description' => $request->description,
            'key_concepts' => $request->key_concepts,
            'facility' => $request->facility,
            'price' => $request->price,
            'place' => $request->place,
            'duration' => $request->duration,
            'operational_start' => $request->operational_start,
            'operational_end' => $request->operational_end,
            'benefit' => $request->benefit
        ]);

        // Sync trainers
        $course->trainers()->sync($request->trainer_ids);

        return new CourseResource(true, 'Data Course Berhasil Diperbarui!', $course);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        // Delete all associated files if they exist
        if ($course->image) {
            Storage::delete('public/courses/' . $course->image);
        }

        if ($course->guidelines) {
            Storage::delete('public/courses/guideline/' . $course->guidelines);
        }

        if ($course->syllabus_path) {
            Storage::delete('public/courses/syllabus/' . $course->syllabus_path);
        }

        if ($course->certificate_example_path) {
            Storage::delete('public/courses/certificates/' . $course->certificate_example_path);
        }

        if ($course->certificate_template_path) {
            Storage::delete('public/certificates/' . $course->certificate_template_path);
        }

        if ($course->schedule_path) {
            Storage::delete('public/courses/schedules/' . $course->schedule_path);
        }

        // Delete course-trainer relationships (pivot table entries will be automatically deleted)
        $course->trainers()->detach();

        // Delete the course
        $course->delete();

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
        $course = Course::with('trainers')->find($id);

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        // Changed trainer_id to use trainers relationship
        $relatedCourses = Course::where('id', '<>', $course->id)
            ->whereHas('trainers', function ($query) use ($course) {
                $query->whereIn('trainers.id', $course->trainers->pluck('id'));
            })
            ->limit(4)
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

    public function editCourseStatus(Request $request, $id)
    {
        // Find the course by ID
        $course = Course::find($id);

        // Check if the course exists
        if (!$course) {
            return redirect()->route('courses.index')->with('error', 'Course not found');
        }

        // Validate the status input
        $request->validate([
            'status' => 'required|in:not_started,ongoing,completed'
        ]);

        // Update the course status
        $course->status = $request->input('status');
        $course->save();

        return response()->json(['data' => $course], 200);
    }

    public function uploadCertificateTemplate(Request $request, $id)
    {
        // Validasi input, pastikan file yang diupload adalah PDF
        $validator = Validator::make($request->all(), [
            'certificate_template' => 'required|file|mimes:pdf|max:10000' // Maksimal 10MB
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Temukan course berdasarkan ID
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course tidak ditemukan'], 404);
        }

        // Simpan file PDF ke dalam storage (misalnya folder 'public/certificates')
        $template = $request->file('certificate_template');
        $template->storeAs('public/certificates', $template->hashName());

        // Update course dengan path template sertifikat
        $course->update([
            'certificate_template_path' => $template->hashName()
        ]);

        return response()->json(['message' => 'Template sertifikat berhasil diupload!', 'data' => $course], 200);
    }

    /**
     * Get course with its modules, concepts, and exercises
     *
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function getCourseWithModules(string $id)
    {
        $course = Course::with(['modules' => function ($query) {
            $query->orderBy('order')
                ->with(['concepts' => function ($q) {
                    $q->orderBy('order');
                }, 'exercises' => function ($q) {
                    $q->orderBy('order');
                }]);
        }])->find($id);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Data Course tidak ditemukan'
            ], 404);
        }

        // Transform the data to be more structured
        $transformedData = [
            'id' => $course->id,
            'name' => $course->name,
            'description' => $course->description,
            'status' => $course->status,
            'modules' => $course->modules->map(function ($module) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'subtitle' => $module->subtitle,
                    'description' => $module->description,
                    'type' => $module->type,
                    'order' => $module->order,
                    'estimated_time_min' => $module->estimated_time_min,
                    'thumbnail_url' => $module->thumbnail_url,
                    'concepts' => $module->concepts->map(function ($concept) {
                        return [
                            'id' => $concept->id,
                            'title' => $concept->title,
                            'order' => $concept->order
                        ];
                    }),
                    'exercises' => $module->exercises->map(function ($exercise) {
                        return [
                            'id' => $exercise->id,
                            'description' => $exercise->description,
                            'order' => $exercise->order
                        ];
                    })
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail Course dengan Modul berhasil dimuat',
            'data' => $transformedData
        ]);
    }
}
