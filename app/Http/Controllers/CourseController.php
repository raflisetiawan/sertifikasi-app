<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('created_at', 'desc')->get();
        return new CourseResource(true, 'List Data Courses', $courses);
    }

    public function store(StoreCourseRequest $request)
    {
        $validated = $request->validated();

        $imageHash = $this->uploadFile($request, 'image', 'public/courses');
        $guidelinesHash = $this->uploadFile($request, 'guidelines', 'public/courses/guideline');
        $syllabusHash = $this->uploadFile($request, 'syllabus', 'public/courses/syllabus');

        $course = Course::create(array_merge($validated, [
            'image' => $imageHash,
            'guidelines' => $guidelinesHash,
            'syllabus_path' => $syllabusHash,
            'status' => 'not_started',
        ]));

        if ($request->has('trainer_ids')) {
            $course->trainers()->sync($request->input('trainer_ids'));
        }

        return new CourseResource(true, 'Data Course Berhasil Ditambahkan!', $course);
    }

    public function show(string $id)
    {
        $course = Course::with('trainers')->find($id);

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        return new CourseResource(true, 'Detail Data course!', $course);
    }

    public function update(UpdateCourseRequest $request, string $id)
    {
        $course = Course::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $this->deleteFile('public/courses/' . $course->image);
            $validated['image'] = $this->uploadFile($request, 'image', 'public/courses');
        }

        if ($request->hasFile('guidelines')) {
            $this->deleteFile('public/courses/guideline/' . $course->guidelines);
            $validated['guidelines'] = $this->uploadFile($request, 'guidelines', 'public/courses/guideline');
        }

        if ($request->hasFile('syllabus')) {
            $this->deleteFile('public/courses/syllabus/' . $course->syllabus_path);
            $validated['syllabus_path'] = $this->uploadFile($request, 'syllabus', 'public/courses/syllabus');
        }

        $course->update($validated);

        if ($request->has('trainer_ids')) {
            $course->trainers()->sync($request->input('trainer_ids'));
        }

        return new CourseResource(true, 'Data Course Berhasil Diperbarui!', $course);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        $this->deleteFile('public/courses/' . $course->image);
        $this->deleteFile('public/courses/guideline/' . $course->guidelines);
        $this->deleteFile('public/courses/syllabus/' . $course->syllabus_path);

        $course->trainers()->detach();
        $course->delete();

        return new CourseResource(true, 'Data Course Berhasil Dihapus!', null);
    }

    public function relatedCourse(string $id)
    {
        $course = Course::with('trainers')->find($id);

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        $relatedCourses = Course::where('id', '<>', $course->id)
            ->whereHas('trainers', function ($query) use ($course) {
                $query->whereIn('trainers.id', $course->trainers->pluck('id'));
            })
            ->limit(4)
            ->get();

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

    public function getCourseTableWithZoomLink()
    {
        $course = Course::select(
            'name',
            'operational_start',
            'operational_end',
            'id',
            'status',
        )->latest()->get();

        if (!$course) {
            return response()->json(['message' => 'Data Course tidak ditemukan'], 404);
        }

        return response()->json(['data' => $course], 200);
    }

    public function getIdAndNameCourse()
    {
        $courses = Course::select('id', 'name')->get();
        return response()->json(['data' => $courses], 200);
    }

    public function editCourseStatus(Request $request, $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return redirect()->route('courses.index')->with('error', 'Course not found');
        }

        $request->validate([
            'status' => 'required|in:not_started,ongoing,completed'
        ]);

        $course->status = $request->input('status');
        $course->save();

        return response()->json(['data' => $course], 200);
    }

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

    private function uploadFile(Request $request, string $fieldName, string $directory): ?string
    {
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);
            $hash = $file->hashName();
            $file->storeAs($directory, $hash);
            return $hash;
        }
        return null;
    }

    private function deleteFile(string $path): void
    {
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }
}
