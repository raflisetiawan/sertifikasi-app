<?php

namespace Database\Factories;

use App\Models\CourseBenefit;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseBenefitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseBenefit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => Course::factory(), // Will be overridden by seeder
            'title' => $this->faker->sentence(3),
            'subtitle' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph,
            'image' => null, // Will be handled by seeder
            'earn_by' => $this->faker->randomElement(['completion', 'exam', 'assignment']),
        ];
    }
}
