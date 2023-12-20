<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            // Generate random number of materials for each course
            $numMaterials = rand(1, 5);

            // Create materials for the current course
            Material::factory($numMaterials)->create([
                'course_id' => $course->id,
            ]);
        }
    }
}
