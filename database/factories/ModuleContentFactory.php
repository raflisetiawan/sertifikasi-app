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
            'module_id' => \App\Models\Module::factory(),
            'title' => $this->faker->sentence,
            'content_type' => $this->faker->randomElement(['text', 'video', 'quiz', 'assignment', 'file']),
            'content_id' => function (array $attributes) {
                // This will be set by the specific content factory (e.g., TextFactory)
                return null;
            },
            'order' => $this->faker->unique()->numberBetween(1, 100),
            'is_required' => $this->faker->boolean,
            'minimum_duration_seconds' => $this->faker->numberBetween(60, 600),
            'completion_rules' => null,
        ];
    }
}
