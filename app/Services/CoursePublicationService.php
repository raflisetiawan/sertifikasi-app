<?php

namespace App\Services;

use App\Enums\CourseStatus;
use App\Models\Course;
use Illuminate\Support\Facades\Log;

class CoursePublicationService
{
    /**
     * Memeriksa apakah kursus memenuhi semua syarat untuk dipublikasikan.
     *
     * @param Course $course
     * @return array
     */
    public function checkPublicationRequirements(Course $course): array
    {
        $errors = [];

        // 1. Periksa apakah ada minimal satu trainer
        if ($course->trainers()->doesntExist()) {
            $errors[] = 'Kursus harus memiliki minimal satu trainer.';
        }

        // 2. Periksa apakah ada minimal satu benefit
        if ($course->courseBenefits()->doesntExist()) {
            $errors[] = 'Kursus harus memiliki minimal satu benefit.';
        }

        // 3. Periksa apakah ada minimal satu modul
        if ($course->modules()->doesntExist()) {
            $errors[] = 'Kursus harus memiliki minimal satu modul.';
        } else {
            // 4. Jika ada modul, periksa setiap modul apakah memiliki konten
            foreach ($course->modules as $module) {
                if ($module->contents()->doesntExist()) {
                    $errors[] = "Modul '{$module->title}' tidak memiliki konten.";
                }
            }
        }

        return [
            'can_publish' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Mempublikasikan kursus jika semua syarat terpenuhi.
     *
     * @param Course $course
     * @return array
     */
    public function publish(Course $course): array
    {
        if ($course->status === CourseStatus::PUBLISHED) {
            return [
                'success' => false,
                'message' => 'Kursus ini sudah berstatus published.',
                'status_code' => 409, // Conflict
            ];
        }

        $checkResult = $this->checkPublicationRequirements($course);

        if (!$checkResult['can_publish']) {
            return [
                'success' => false,
                'message' => 'Kursus tidak memenuhi syarat untuk dipublikasikan.',
                'errors' => $checkResult['errors'],
                'status_code' => 422, // Unprocessable Entity
            ];
        }

        try {
            $course->status = CourseStatus::PUBLISHED;
            $course->save();

            return [
                'success' => true,
                'message' => 'Kursus berhasil dipublikasikan.',
                'status_code' => 200,
            ];
        } catch (\Exception $e) {
            Log::error("Gagal mempublikasikan kursus #{$course->id}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mempublikasikan kursus.',
                'status_code' => 500,
            ];
        }
    }
}
