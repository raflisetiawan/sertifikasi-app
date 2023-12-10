<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 9; $i++) {
            DB::table('trainers')->insert([
                'name' => "Trainer {$i}",
                'email' => "trainer{$i}@example.com",
                'qualification' => "Qualification {$i}",
                'description' => "Description of Trainer {$i}",
                'image' => null, // Empty image
                'starred' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
