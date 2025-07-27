<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4, true, 'id_ID'),
            'description' => $this->faker->paragraph(3, true, 'id_ID'),
            'video_url' => $this->faker->randomElement([
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'https://www.youtube.com/watch?v=3JZ_D3ELwOQ',
                'https://www.youtube.com/watch?v=2Vv-BfVoq4g',
                'https://www.youtube.com/watch?v=LXb3EKWsInQ',
            ]),
            'provider' => 'youtube',
            'video_id' => $this->faker->randomElement([
                'dQw4w9WgXcQ',
                '3JZ_D3ELwOQ',
                '2Vv-BfVoq4g',
                'LXb3EKWsInQ',
            ]),
            'duration_seconds' => $this->faker->numberBetween(60, 1800),
            'thumbnail_url' => null,
            'is_downloadable' => $this->faker->boolean(30),
            'captions' => null,
        ];
    }
}
