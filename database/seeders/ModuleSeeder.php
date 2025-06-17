<?php

namespace Database\Seeders;

use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $courseId = 1; // Assuming course ID is 1

        $modules = [
            [
                'course_id' => $courseId,
                'order' => 1,
                'type' => 'prework',
                'estimated_time_min' => 60,
                'title' => 'Introduction to Laravel',
                'subtitle' => 'Getting Started with Laravel',
                'description' => 'Learn the basics of Laravel framework',
                'is_access_restricted' => false, // Always accessible
                'access_start_at' => null,
                'access_end_at' => null
            ],
            [
                'course_id' => $courseId,
                'order' => 2,
                'type' => 'module',
                'estimated_time_min' => 120,
                'title' => 'Database and Migration',
                'subtitle' => 'Working with Databases',
                'description' => 'Learn database management in Laravel',
                'is_access_restricted' => true, // Time-restricted access
                'access_start_at' => Carbon::now(),
                'access_end_at' => Carbon::now()->addDays(7) // Available for 7 days
            ],
            [
                'course_id' => $courseId,
                'order' => 3,
                'type' => 'module',
                'estimated_time_min' => 90,
                'title' => 'Authentication & Authorization',
                'subtitle' => 'User Management in Laravel',
                'description' => 'Implement user authentication and authorization',
                'is_access_restricted' => true,
                'access_start_at' => Carbon::now()->addDays(7), // Starts after module 2
                'access_end_at' => Carbon::now()->addDays(14) // Available for 7 days
            ],
            [
                'course_id' => $courseId,
                'order' => 4,
                'type' => 'final',
                'estimated_time_min' => 180,
                'title' => 'Final Project',
                'subtitle' => 'Build a Complete Web Application',
                'description' => 'Apply your knowledge in a real project',
                'is_access_restricted' => true,
                'access_start_at' => Carbon::now()->addDays(14), // Starts after module 3
                'access_end_at' => Carbon::now()->addDays(30) // Available for 16 days
            ]
        ];

        // Create modules with different access patterns
        foreach ($modules as $moduleData) {
            $module = Module::create($moduleData);

            // Log creation for verification
            $this->command->info("Created module: {$module->title}");
            if ($module->is_access_restricted) {
                $this->command->info("Access period: {$module->access_start_at} to {$module->access_end_at}");
            }
        }
    }
}
