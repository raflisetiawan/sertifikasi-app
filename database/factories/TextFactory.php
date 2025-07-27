<?php

namespace Database\Factories;

use App\Models\Text;
use Illuminate\Database\Eloquent\Factories\Factory;

class TextFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Text::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(5, true, 'id_ID'),
            'content' => $this->faker->paragraphs(rand(3, 7), true, 'id_ID'),
            'format' => $this->faker->randomElement(['html', 'markdown']),
        ];
    }
}
