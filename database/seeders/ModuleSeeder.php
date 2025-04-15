<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $courseId = 1; // Assuming the course ID is 1

        $modules = [
            [
                'course_id' => $courseId,
                'order' => 1,
                'type' => 'prework',
                'estimated_time_min' => 60,
                'title' => 'Introduction to Laravel',
                'subtitle' => 'Getting Started with Laravel',
                'description' => 'Learn the basics of Laravel framework'
            ],
            [
                'course_id' => $courseId,
                'order' => 2,
                'type' => 'module',
                'estimated_time_min' => 120,
                'title' => 'Database and Migration',
                'subtitle' => 'Working with Databases',
                'description' => 'Learn database management in Laravel'
            ],
            [
                'course_id' => $courseId,
                'order' => 3,
                'type' => 'final',
                'estimated_time_min' => 180,
                'title' => 'Final Project',
                'subtitle' => 'Build a Complete Web Application',
                'description' => 'Apply your knowledge in a real project'
            ]
        ];

        foreach ($modules as $module) {
            Module::create($module);
        }
    }
}
