<?php

namespace Database\Factories;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TrainerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Trainer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'qualification' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'starred' => true, // 30% chance of being starred
            'user_id' => User::factory()->create([
                'email' => $this->faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'role_id' => 2, // Assuming role_id 2 is for trainers
            ])->id,
        ];
    }
}
