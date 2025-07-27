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
            'title' => 'Tugas: ' . $this->faker->sentence(3, true, 'id_ID'),
            'description' => $this->faker->paragraph(3, true, 'id_ID'),
            'instructions' => $this->faker->paragraph(5, true, 'id_ID'),
            'submission_requirements' => [
                'format' => $this->faker->randomElement(['pdf', 'doc', 'docx', 'zip', 'rar', 'ppt', 'pptx', 'xls', 'xlsx']),
                'pages' => $this->faker->numberBetween(1, 10),
                'word_count' => $this->faker->numberBetween(500, 2000),
            ],
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'max_file_size_mb' => $this->faker->numberBetween(5, 50),
            'allowed_file_types' => $this->faker->randomElements(['pdf', 'doc', 'docx', 'zip', 'rar', 'ppt', 'pptx', 'xls', 'xlsx'], 3),
        ];
    }
}
