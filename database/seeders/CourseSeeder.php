<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Trainer;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $trainers = Trainer::all();

        if ($trainers->isEmpty()) {
            $this->command->info('No trainers found, please seed trainers first.');
            return;
        }

        // Ensure the destination directory exists
        Storage::disk('public')->makeDirectory('courses');

        $sourcePath = public_path('img/course-example');
        $imageFiles = \Illuminate\Support\Facades\File::files($sourcePath);

        if (empty($imageFiles)) {
            $this->command->info('No images found in public/img/course-example.');
            return;
        }

        for ($i = 0; $i < 8; $i++) {
            $randomImage = $imageFiles[array_rand($imageFiles)];
            
            // Store the file and get the new path (e.g., courses/unique_filename.jpg)
            $fullPath = Storage::disk('public')->putFile('courses', new File($randomImage->getRealPath()));
            
            // Extract only the filename
            $filename = basename($fullPath);

            $course = Course::factory()->create([
                'image' => $filename,
            ]);

            $course->trainers()->attach($trainers->random(rand(1, 2))->pluck('id')->toArray());
        }
    }
}
