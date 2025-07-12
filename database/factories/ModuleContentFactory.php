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
            'title' => $this->faker->sentence,
            'content_type' => null, // Will be set by seeder
            'content_id' => null, // Will be set by seeder
            'order' => $this->faker->numberBetween(1, 100),
            'is_required' => $this->faker->boolean,
            'minimum_duration_seconds' => null,
            'completion_rules' => null,
        ];
    }
}
