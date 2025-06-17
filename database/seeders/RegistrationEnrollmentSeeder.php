<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;

class RegistrationEnrollmentSeeder extends Seeder
{
    public function run()
    {
        // Get user and course
        $user = User::where('email', 'user@gmail.com')->firstOrFail();
        $course = Course::first();

        // Create registration
        $registration = Registration::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'verification' => true,
            'verified_at' => now(),
            'verified_by' => 1, // Admin user ID
            'status' => 'active'
        ]);

        // Create payment
        Payment::create([
            'registration_id' => $registration->id,
            'midtrans_order_id' => 'TEST-' . time(),
            'transaction_id' => 'test-transaction-' . time(),
            'payment_type' => 'bank_transfer',
            'transaction_time' => now(),
            'gross_amount' => $course->price,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'snap_token' => 'test-token',
            'payment_url' => 'http://example.com',
            'payment_details' => [
                'bank' => 'bca',
                'va_number' => '123456789'
            ]
        ]);

        // Create enrollment
        $enrollment = $registration->enrollment()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'started_at' => now(),
            'progress_percentage' => 0
        ]);

        // Create module progress for each module
        $modules = Module::where('course_id', $course->id)->get();

        foreach ($modules as $index => $module) {
            // First module is started
            $started = $index === 0;

            $enrollment->moduleProgresses()->create([
                'module_id' => $module->id,
                'status' => $started ? 'active' : 'locked',
                'progress_percentage' => $started ? 0 : 0,
                'started_at' => $started ? now() : null,
                'completed_at' => null
            ]);
        }

        // Log seeding completion
        $this->command->info('Created enrollment and module progress for user: ' . $user->email);
    }
}
