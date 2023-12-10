<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('faqs')->insert([
            'question' => 'What is the course enrollment process?',
            'answer' => 'To enroll in a course, visit the course page, click on the "Enroll" button, and follow the instructions to complete the enrollment process.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('faqs')->insert([
            'question' => 'How long do I have access to a course?',
            'answer' => 'Once you enroll in a course, you have lifetime access to the course materials, including any updates or additions made in the future.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
