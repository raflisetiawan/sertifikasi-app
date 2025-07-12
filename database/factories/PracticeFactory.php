<?php

namespace Database\Factories;

use App\Models\Practice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PracticeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Practice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3) . ' Practice',
            'description' => $this->faker->paragraph,
            'time_limit_minutes' => $this->faker->numberBetween(10, 45),
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
                    'answer_key' => $this->faker->numberBetween(0, 3),
                ],
                [
                    'question' => $this->faker->sentence . '?',
                    'type' => 'true_false',
                    'options' => ['True', 'False'],
                    'answer_key' => $this->faker->numberBetween(0, 1),
                ],
            ],
        ];
    }
}
