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
        $categories = ['data-science', 'digital-marketing', 'full-stack', 'mobile', 'seo', 'sosmed', 'ui-ux', 'writing'];
        $category = $this->faker->randomElement($categories);
        $thumbnail = "{$category}-{$this->faker->numberBetween(1, 4)}.jpg";

        return [
            'course_id' => null, // Will be set by seeder
            'title' => $this->faker->sentence(3, true),
            'order' => $this->faker->numberBetween(1, 100), // Will be set by seeder
            'type' => $this->faker->randomElement(['prework', 'module', 'final']),
            'estimated_time_min' => $this->faker->numberBetween(10, 120),
            'subtitle' => $this->faker->sentence(5, true),
            'description' => $this->faker->paragraph(3, true),
            'thumbnail' => $thumbnail,
            'is_access_restricted' => false,
            'access_start_at' => null,
            'access_end_at' => null,
        ];
    }
}
