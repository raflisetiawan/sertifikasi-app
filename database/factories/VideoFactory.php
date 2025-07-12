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
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'video_url' => 'https://www.youtube.com/watch?v=' . $this->faker->regexify('[A-Za-z0-9_-]{11}'),
            'provider' => 'youtube',
            'video_id' => $this->faker->regexify('[A-Za-z0-9_-]{11}'),
            'duration_seconds' => $this->faker->numberBetween(60, 1800),
            'thumbnail_url' => null,
            'is_downloadable' => $this->faker->boolean(30),
            'captions' => null,
        ];
    }
}
