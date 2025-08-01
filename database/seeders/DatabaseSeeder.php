<?php

use Database\Seeders\BenefitSeeder;
use Database\Seeders\CourseSeeder;
use Database\Seeders\FaqSeeder;

use Database\Seeders\ModuleContentSeeder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RegistrationEnrollmentSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SingleCourseEnrollmentSeeder;
use Database\Seeders\TrainerSeeder;
use Database\Seeders\TrainerUserSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            TrainerUserSeeder::class,
            FaqSeeder::class,
            TrainerSeeder::class,
            CourseSeeder::class,
            BenefitSeeder::class,
            ModuleSeeder::class,
            ModuleContentSeeder::class,
            RegistrationEnrollmentSeeder::class,
            SingleCourseEnrollmentSeeder::class,
        ]);
    }
}
