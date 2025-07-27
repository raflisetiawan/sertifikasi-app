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
            'title' => 'Latihan: ' . $this->faker->sentence(3, true, 'id_ID'),
            'description' => $this->faker->paragraph(3, true, 'id_ID'),
            'time_limit_minutes' => $this->faker->numberBetween(10, 45),
            'questions' => [
                [
                    'question' => 'Apa yang dimaksud dengan ' . $this->faker->word('id_ID') . '?',
                    'type' => 'multiple_choice',
                    'options' => [
                        $this->faker->sentence(3, true, 'id_ID'),
                        $this->faker->sentence(3, true, 'id_ID'),
                        $this->faker->sentence(3, true, 'id_ID'),
                        $this->faker->sentence(3, true, 'id_ID'),
                    ],
                    'answer_key' => $this->faker->numberBetween(0, 3),
                ],
                [
                    'question' => 'Pernyataan ini benar atau salah: ' . $this->faker->sentence(5, true, 'id_ID') . '?',
                    'type' => 'true_false',
                    'options' => ['Benar', 'Salah'],
                    'answer_key' => $this->faker->numberBetween(0, 1),
                ],
                [
                    'question' => 'Isi bagian yang kosong: ' . $this->faker->sentence(4, true, 'id_ID') . '...',
                    'type' => 'fill_in_the_blank',
                    'answer_key' => $this->faker->word('id_ID'),
                ]
            ],
        ];
    }
}
