<?php

namespace Database\Seeders;

use App\Models\Trainer;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class TrainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the destination directory exists
        Storage::disk('public')->makeDirectory('trainers/images');

        $sourcePath = public_path('img/trainer-example');
        $imageFiles = \Illuminate\Support\Facades\File::files($sourcePath);

        if (empty($imageFiles)) {
            $this->command->info('No images found in public/img/trainer-example.');
            return;
        }

        for ($i = 0; $i < 4; $i++) {
            $randomImage = $imageFiles[array_rand($imageFiles)];
            
            // Store the file and get the new path (e.g., trainers/images/unique_filename.jpg)
            $fullPath = Storage::disk('public')->putFile('trainers/images', new File($randomImage->getRealPath()));
            
            Trainer::factory()->create([
                'image' => $fullPath,
            ]);
        }
    }
}
