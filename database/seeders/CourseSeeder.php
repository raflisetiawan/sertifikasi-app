<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        Course::create([
            'name' => 'Laravel Web Development',
            'description' => 'Learn web development using Laravel framework',
            'key_concepts' => [
                'MVC Architecture',
                'Database Migration',
                'Authentication & Authorization',
                'API Development'
            ],
            'facility' => [
                'Online Learning Platform',
                'Live Mentoring',
                'Project-based Learning',
                'Certificate'
            ],
            'price' => 1000,
            'place' => 'Online',
            'duration' => '3 months',
            'status' => 'active',
            'operational_start' => now(),
            'operational_end' => now()->addMonths(3),
            'benefit' => 'Get hands-on experience in building web applications',
            'guidelines' => 'Basic PHP knowledge required'
        ]);
    }
}
