<?php

namespace Database\Factories;

use App\Models\ModuleContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ModuleContent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'module_id' => null, // Will be set by seeder
            'title' => null, // Will be set by seeder
            'content_type' => $this->faker->randomElement(['video', 'text', 'file', 'quiz', 'assignment', 'practice']),
            'content_id' => null, // Will be set by seeder
            'order' => null, // Will be set by seeder
            'is_required' => $this->faker->boolean(80), // 80% chance of being required
            'minimum_duration_seconds' => null,
            'completion_rules' => null,
        ];
    }
}
