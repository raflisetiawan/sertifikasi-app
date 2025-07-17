<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index()
    {
        $courses = Course::orderBy('created_at', 'desc')->get();
        return new CourseResource(true, 'List Data Courses', $courses);
    }

    public function store(StoreCourseRequest $request)
    {
        $course = $this->courseService->createCourse($request->validated());
        return new CourseResource(true, 'Data Course Berhasil Ditambahkan!', $course);
    }

    public function show(Course $course)
    {
        $course->load('trainers');
        return new CourseResource(true, 'Detail Data course!', $course);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $updatedCourse = $this->courseService->updateCourse($course, $request->validated());
        return new CourseResource(true, 'Data Course Berhasil Diperbarui!', $updatedCourse);
    }

    public function destroy(Course $course)
    {
        $this->courseService->deleteCourse($course);
        return new CourseResource(true, 'Data Course Berhasil Dihapus!', null);
    }

    public function relatedCourse(Course $course)
    {
        $course->load('trainers');
        $relatedCourses = Course::where('id', '<>', $course->id)
            ->whereHas('trainers', function ($query) use ($course) {
                $query->whereIn('trainers.id', $course->trainers->pluck('id'));
            })
            ->limit(4)
            ->get();

        if ($relatedCourses->isEmpty()) {
            $randomCourses = Course::where('id', '<>', $course->id)->inRandomOrder()->limit(4)->get();
            return new CourseResource(true, 'Random Courses', $randomCourses);
        }

        return new CourseResource(true, 'Related Courses', $relatedCourses);
    }

    public function getCourseNameById(Course $course)
    {
        return response()->json(['course_name' => $course->name], 200);
    }

    public function getCourseTableWithZoomLink(Request $request)
    {
        $query = Course::select('name', 'operational_start', 'operational_end', 'id', 'status')->latest();

        if ($request->has('status') && in_array($request->status, ['not_started', 'ongoing', 'completed'])) {
            $query->where('status', $request->status);
        }
        if ($request->has('start_date')) {
            $query->whereDate('operational_start', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('operational_start', '<=', $request->end_date);
        }

        $courses = $query->get();

        if ($courses->isEmpty()) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        return response()->json(['data' => $courses], 200);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:courses,id',
            'status' => 'required|in:not_started,ongoing,completed',
        ]);

        Course::whereIn('id', $request->ids)->update(['status' => $request->status]);

        return response()->json(['message' => 'Status kursus berhasil diperbarui.'], 200);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:courses,id',
        ]);

        $courses = Course::whereIn('id', $request->ids)->get();

        foreach ($courses as $course) {
            $this->courseService->deleteCourse($course);
        }

        return response()->json(['message' => 'Kursus berhasil dihapus.'], 200);
    }

    public function getIdAndNameCourse()
    {
        $courses = Course::select('id', 'name')->get();
        if ($courses->isEmpty()) {
            return response()->json(['message' => 'Tidak ada kursus yang ditemukan'], 404);
        }
        return response()->json(['data' => $courses], 200);
    }

    public function editCourseStatus(Request $request, Course $course)
    {
        $request->validate([
            'status' => 'required|in:not_started,ongoing,completed'
        ]);

        $course->status = $request->input('status');
        $course->save();

        return response()->json(['data' => $course], 200);
    }

    public function getCourseWithModules(Course $course)
    {
        $course->load(['modules' => function ($query) {
            $query->orderBy('order')
                ->with(['concepts' => function ($q) {
                    $q->orderBy('order');
                }, 'exercises' => function ($q) {
                    $q->orderBy('order');
                }]);
        }]);

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
