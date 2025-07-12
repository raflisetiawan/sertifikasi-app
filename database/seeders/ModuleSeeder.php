<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    protected $faker;

    public function run()
    {
        $this->faker = \Faker\Factory::create();
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->info('No courses found, please seed courses first.');
            return;
        }

        foreach ($courses as $course) {
            $this->command->info("Seeding modules for course: {$course->name} (ID: {$course->id})");

            $modulesData = [
                [
                    'type' => 'prework',
                    'title' => 'Introduction to ' . $course->name,
                    'subtitle' => 'Getting Started',
                    'description' => $this->faker->paragraph,
                    'is_access_restricted' => false,
                    'access_start_at' => null,
                    'access_end_at' => null
                ],
                [
                    'type' => 'module',
                    'title' => 'Core Concepts of ' . $course->name,
                    'subtitle' => 'Deep Dive',
                    'description' => $this->faker->paragraph,
                    'is_access_restricted' => true,
                    'access_start_at' => Carbon::now(),
                    'access_end_at' => Carbon::now()->addDays(7)
                ],
                [
                    'type' => 'module',
                    'title' => 'Advanced Topics in ' . $course->name,
                    'subtitle' => 'Mastering Skills',
                    'description' => $this->faker->paragraph,
                    'is_access_restricted' => true,
                    'access_start_at' => Carbon::now()->addDays(7),
                    'access_end_at' => Carbon::now()->addDays(14)
                ],
                [
                    'type' => 'final',
                    'title' => 'Final Project for ' . $course->name,
                    'subtitle' => 'Apply Your Knowledge',
                    'description' => $this->faker->paragraph,
                    'is_access_restricted' => true,
                    'access_start_at' => Carbon::now()->addDays(14),
                    'access_end_at' => Carbon::now()->addDays(30)
                ]
            ];

            foreach ($modulesData as $order => $moduleData) {
                Module::factory()->create(array_merge($moduleData, [
                    'course_id' => $course->id,
                    'order' => $order + 1,
                    'estimated_time_min' => $this->faker->numberBetween(60, 180),
                ]));
            }
        }
    }
}
