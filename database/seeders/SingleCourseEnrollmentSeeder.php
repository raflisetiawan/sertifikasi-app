<?php

namespace Database\Seeders;

use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\ModuleProgress;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Text;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SingleCourseEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a new User
        $user = User::firstOrCreate(
            ['email' => 'singlecourseuser@example.com'],
            [
                'name' => 'Single Course User',
                'password' => Hash::make('password'),
                'phone_number' => '081234567890',
                'role_id' => 2, // Assuming role_id 2 is for students
                'email_verified_at' => now(),
            ]
        );

        // 2. Create a new Course
        $course = Course::firstOrCreate(
            ['name' => 'Single Module Course for Admin Review'],
            [
                'description' => 'A course designed for testing admin review process.',
                'key_concepts' => ['Concept 1'],
                'facility' => ['Online'],
                'price' => 500,
                'place' => 'Online',
                'duration' => '1 week',
                'status' => 'published',
                'operational_start' => now(),
                'operational_end' =>    now()->addWeek(),
            ]
        );

        // 3. Create a single Module for this new course
        $module = Module::firstOrCreate(
            [
                'course_id' => $course->id,
                'title' => 'Introduction to Admin Review Flow',
            ],
            [
                'order' => 1,
                'type' => 'module',
                'estimated_time_min' => 30,
                'subtitle' => 'Understanding the process',
                'description' => 'This module explains the admin review process.',
                'is_access_restricted' => false,
                'access_start_at' => null,
                'access_end_at' => null
            ]
        );

        // 4. Create a ModuleContent of type Text for this module
        $textContent = Text::firstOrCreate(
            ['title' => 'Welcome to the Admin Review Module'],
            ['content' => 'This is the content for the admin review module.']
        );

        $moduleContent = ModuleContent::firstOrCreate(
            [
                'module_id' => $module->id,
                'content_type' => 'text',
                'content_id' => $textContent->id,
            ],
            [
                'order' => 1,
                'title' => 'Admin Review Overview',
                'minimum_duration_seconds' => 600, // 10 minutes * 60 seconds
            ]
        );

        // 5. Create a Registration for the user and the new course
        $registration = Registration::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'verification' => true,
                'verified_at' => now(),
                'verified_by' => 1, // Assuming admin user ID is 1
                'status' => 'active'
            ]
        );

        // 6. Create Payment for the registration
        Payment::firstOrCreate(
            ['registration_id' => $registration->id],
            [
                'midtrans_order_id' => 'SINGLE-COURSE-' . time(),
                'transaction_id' => 'single-course-transaction-' . time(),
                'payment_type' => 'bank_transfer',
                'transaction_time' => now(),
                'gross_amount' => $course->price,
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'snap_token' => 'single-course-token',
                'payment_url' => 'http://example.com/single-course-payment',
                'payment_details' => ['bank' => 'bca', 'va_number' => '0987654321']
            ]
        );

        // 7. Create an Enrollment for the user and the new course
        $enrollment = Enrollment::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'registration_id' => $registration->id,
            ],
            [
                'status' => 'active',
                'started_at' => now(),
                'progress_percentage' => 0
            ]
        );

        // 8. Create ModuleProgress for the single module, setting it to completed
        $moduleProgress = ModuleProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'module_id' => $module->id,
            ],
            [
                'status' => 'completed',
                'progress_percentage' => 100.0,
                'started_at' => now()->subDay(),
                'completed_at' => now(),
            ]
        );

        // 9. Create ContentProgress for the single text content, setting it to completed
        ContentProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'module_content_id' => $moduleContent->id,
            ],
            [
                'status' => 'completed',
                'completed_at' => now(),
                'started_at' => now()->subDay(),
                'score' => 100.0,
                'attempts' => 1,
                'last_attempt_at' => now(),
            ]
        );

        // Manually trigger updateProgress to ensure enrollment status is updated
        // This is important because the seeder might run outside of a request cycle
        // where the observer/event listener for ContentProgress might not fire immediately.
        $enrollment->updateProgress(100.0);

        $this->command->info('Single Course Enrollment Seeder completed.');
        $this->command->info('User: ' . $user->email);
        $this->command->info('Course: ' . $course->name);
        $this->command->info('Enrollment Status: ' . $enrollment->fresh()->status);
    }
}
