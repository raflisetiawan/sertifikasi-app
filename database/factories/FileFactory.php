<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'file_path' => 'files/' . $this->faker->uuid . '.pdf',
            'file_name' => $this->faker->word . '.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(100, 5000),
            'description' => $this->faker->paragraph,
        ];
    }
}
