<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Trainer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Full-Stack Web Developer', 'Data Science with Python', 'Digital Marketing Masterclass', 'UI/UX Design Fundamentals', 'Social Media Specialist', 'Content Writing for Beginners', 'SEO Expert Course', 'Mobile App Development with Flutter']),
            'description' => $this->faker->paragraph(4),
            'key_concepts' => $this->faker->randomElements(['MVC', 'OOP', 'Data Structures', 'Algorithms', 'API', 'Database'], 3),
            'facility' => $this->faker->randomElements(['E-Certificate', 'Portfolio', 'Job Connector', 'Mentoring'], 2),
            'price' => $this->faker->numberBetween(500000, 2000000),
            'place' => 'Online',
            'duration' => $this->faker->numberBetween(4, 12) . ' weeks',
            'status' => 'active',
            'operational_start' => now(),
            'operational_end' => now()->addMonths(6),
            'benefit' => $this->faker->sentence,
            'guidelines' => $this->faker->sentence,
        ];
    }
}
