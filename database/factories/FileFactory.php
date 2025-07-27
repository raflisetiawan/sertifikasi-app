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
            'title' => 'Dokumen: ' . $this->faker->sentence(3, true, 'id_ID'),
            'file_path' => 'files/' . $this->faker->uuid . '.' . $this->faker->randomElement(['pdf', 'docx', 'xlsx', 'pptx']),
            'file_name' => $this->faker->word . '.' . $this->faker->randomElement(['pdf', 'docx', 'xlsx', 'pptx']),
            'mime_type' => $this->faker->randomElement(['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation']),
            'file_size' => $this->faker->numberBetween(100, 5000),
            'description' => $this->faker->paragraph(3, true, 'id_ID'),
        ];
    }
}
