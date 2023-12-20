<?php

use Database\Seeders\FaqSeeder;
use Database\Seeders\MaterialsSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TrainerSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Call the RoleSeeder
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(TrainerSeeder::class);
        $this->call(MaterialsSeeder::class);
        // Add other seeders here...
    }
}
