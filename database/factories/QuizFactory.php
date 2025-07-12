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
            'title' => $this->faker->sentence(3) . ' Quiz',
            'description' => $this->faker->paragraph,
            'time_limit_minutes' => $this->faker->numberBetween(10, 60),
            'passing_score' => $this->faker->numberBetween(60, 80),
            'max_attempts' => $this->faker->numberBetween(1, 5),
            'questions' => [
                [
                    'question' => $this->faker->sentence . '?',
                    'type' => 'multiple_choice',
                    'options' => [
                        $this->faker->word,
                        $this->faker->word,
                        $this->faker->word,
                        $this->faker->word,
                    ],
                    'correct_answer' => $this->faker->numberBetween(0, 3),
                    'score' => 10,
                ],
                [
                    'question' => $this->faker->sentence . '?',
                    'type' => 'true_false',
                    'options' => ['True', 'False'],
                    'correct_answer' => $this->faker->numberBetween(0, 1),
                    'score' => 10,
                ],
            ],
        ];
    }
}
