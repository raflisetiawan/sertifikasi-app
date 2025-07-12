<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrainerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainerRoleId = Role::where('name', 'trainer')->first()->id;

        $user = User::create([
            'name' => 'Trainer User',
            'email' => 'trainer@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $trainerRoleId,
            'email_verified_at' => now(),
        ]);

        Trainer::create([
            'user_id' => $user->id,
            'name' => 'Trainer User',
            'email' => 'trainer@example.com',
            'qualification' => 'Certified Trainer',
            'description' => 'Experienced trainer in various fields.',
            'starred' => true,
        ]);
    }
}
