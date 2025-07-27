<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Quiz::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'Kuis: ' . $this->faker->sentence(3, true, 'id_ID'),
            'description' => $this->faker->paragraph(3, true, 'id_ID'),
            'time_limit_minutes' => $this->faker->numberBetween(10, 60),
            'passing_score' => $this->faker->numberBetween(60, 80),
            'max_attempts' => $this->faker->numberBetween(1, 5),
            'questions' => [
                [
                    'question' => 'Pilihlah jawaban yang paling tepat untuk pertanyaan ini: ' . $this->faker->sentence(5, true, 'id_ID') . '?',
                    'type' => 'multiple_choice',
                    'options' => [
                        $this->faker->sentence(3, true, 'id_ID'),
                        $this->faker->sentence(3, true, 'id_ID'),
                        $this->faker->sentence(3, true, 'id_ID'),
                        $this->faker->sentence(3, true, 'id_ID'),
                    ],
                    'correct_answer' => $this->faker->numberBetween(0, 3),
                    'score' => 10,
                ],
                [
                    'question' => 'Apakah pernyataan berikut benar atau salah: ' . $this->faker->sentence(5, true, 'id_ID') . '?',
                    'type' => 'true_false',
                    'options' => ['Benar', 'Salah'],
                    'correct_answer' => $this->faker->numberBetween(0, 1),
                    'score' => 10,
                ],
                [
                    'question' => 'Jelaskan secara singkat: ' . $this->faker->sentence(4, true, 'id_ID') . '?',
                    'type' => 'essay',
                    'score' => 20,
                ]
            ],
        ];
    }
}
