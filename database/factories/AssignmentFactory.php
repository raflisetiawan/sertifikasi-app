<?php

namespace Database\Factories;

use App\Models\Assignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Assignment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3) . ' Assignment',
            'description' => $this->faker->paragraph,
            'instructions' => $this->faker->paragraph(3),
            'submission_requirements' => [
                'format' => $this->faker->randomElement(['pdf', 'doc', 'zip']),
                'pages' => $this->faker->numberBetween(1, 10),
            ],
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'max_file_size_mb' => $this->faker->numberBetween(5, 50),
            'allowed_file_types' => $this->faker->randomElements(['pdf', 'doc', 'docx', 'zip', 'rar'], 2),
        ];
    }
}
