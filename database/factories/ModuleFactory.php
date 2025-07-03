<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Module::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => \App\Models\Course::factory(),
            'title' => $this->faker->sentence,
            'order' => $this->faker->unique()->numberBetween(1, 100),
            'type' => $this->faker->randomElement(['prework', 'module', 'final']),
            'estimated_time_min' => $this->faker->numberBetween(10, 120),
            'subtitle' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'thumbnail' => null,
            'is_access_restricted' => false,
            'access_start_at' => null,
            'access_end_at' => null,
        ];
    }
}
