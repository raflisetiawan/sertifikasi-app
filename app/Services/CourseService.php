<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class CourseService
{
    /**
     * Handle the creation of a new course with file uploads.
     *
     * @param array $data
     * @return Course
     */
    public function createCourse(array $data): Course
    {
        $filePaths = $this->handleFileUploads($data);

        $course = Course::create(array_merge($data, $filePaths, ['status' => 'not_started']));

        if (isset($data['trainer_ids'])) {
            $course->trainers()->sync($data['trainer_ids']);
        }

        return $course;
    }

    /**
     * Handle the update of a course with file uploads.
     *
     * @param Course $course
     * @param array $data
     * @return Course
     */
    public function updateCourse(Course $course, array $data): Course
    {
        $filePaths = $this->handleFileUploads($data, $course);

        $course->update(array_merge($data, $filePaths));

        if (isset($data['trainer_ids'])) {
            $course->trainers()->sync($data['trainer_ids']);
        }

        return $course;
    }

    /**
     * Handle the deletion of a course and its associated files.
     *
     * @param Course $course
     * @return void
     */
    public function deleteCourse(Course $course): void
    {
        $this->deleteCourseFiles($course);
        $course->trainers()->detach();
        $course->delete();
    }

    /**
     * Upload course files and return their paths.
     *
     * @param array $data
     * @param Course|null $existingCourse
     * @return array
     */
    private function handleFileUploads(array $data, ?Course $existingCourse = null): array
    {
        $filePaths = [];
        $fileFields = [
            'image' => 'public/courses',
            'guidelines' => 'public/courses/guideline',
            'syllabus' => 'public/courses/syllabus'
        ];
        $dbFields = [
            'image' => 'image',
            'guidelines' => 'guidelines',
            'syllabus' => 'syllabus_path'
        ];

        foreach ($fileFields as $field => $directory) {
            if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                // Delete old file if it exists
                if ($existingCourse) {
                    $dbField = $dbFields[$field];
                    $this->deleteFile($directory . '/' . $existingCourse->$dbField);
                }
                $filePaths[$dbFields[$field]] = $this->uploadFile($data[$field], $directory);
            }
        }

        return $filePaths;
    }

    /**
     * Store the uploaded file and return its hashed name.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string
     */
    private function uploadFile(UploadedFile $file, string $directory): string
    {
        $hash = $file->hashName();
        $file->storeAs($directory, $hash);
        return $hash;
    }

    /**
     * Delete the specified file from storage.
     *
     * @param string|null $path
     * @return void
     */
    private function deleteFile(?string $path): void
    {
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    /**
     * Delete all files associated with a course.
     *
     * @param Course $course
     * @return void
     */
    private function deleteCourseFiles(Course $course): void
    {
        $this->deleteFile('public/courses/' . $course->image);
        $this->deleteFile('public/courses/guideline/' . $course->guidelines);
        $this->deleteFile('public/courses/syllabus/' . $course->syllabus_path);
    }
}
