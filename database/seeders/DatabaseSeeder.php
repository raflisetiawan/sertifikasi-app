<?php

use Database\Seeders\CourseSeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\MaterialsSeeder;
use Database\Seeders\ModuleContentSeeder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RegistrationEnrollmentSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TrainerSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            FaqSeeder::class,
            TrainerSeeder::class,
            MaterialsSeeder::class,
            CourseSeeder::class,
            ModuleSeeder::class,
            ModuleContentSeeder::class,
            RegistrationEnrollmentSeeder::class,
        ]);
    }
}
