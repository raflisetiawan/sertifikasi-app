<?php

namespace Database\Factories;

use App\Models\Course;
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
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'facility' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'place' => $this->faker->city,
            'duration' => $this->faker->word,
            'operational_start' => $this->faker->date(),
            'operational_end' => $this->faker->date(),
            'image' => null,
        ];
    }
}
