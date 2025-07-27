<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseBenefit;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class BenefitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->info('No courses found, please seed courses first.');
            return;
        }

        // Ensure the destination directory exists
        Storage::disk('public')->makeDirectory('benefits');

        $sourcePath = public_path('img/certificate-example');
        $imageFiles = \Illuminate\Support\Facades\File::files($sourcePath);

        if (empty($imageFiles)) {
            $this->command->info('No images found in public/img/certificate-example.');
            return;
        }

        foreach ($courses as $course) {
            // Create 2 benefits for each course
            $randomImage = $imageFiles[array_rand($imageFiles)];

            // Store the file and get the new path (e.g., benefits/unique_filename.jpg)
            $fullPath = Storage::disk('public')->putFile('benefits', new File($randomImage->getRealPath()));

            CourseBenefit::factory()->create([
                'course_id' => $course->id,
                'image' => $fullPath,
            ]);
        }
    }
}
