<?php

namespace App\Services;

use App\Models\ContentProgress;
use App\Models\Enrollment;
use App\Models\ModuleProgress;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentService
{
    public function enrollUserAfterPayment(Registration $registration)
    {
        try {
            DB::beginTransaction();

            // Check if already enrolled
            if ($registration->enrollment()->exists()) {
                throw new \Exception('User is already enrolled in this course');
            }

            // Check if course is still active
            if ($registration->course->status !== 'active') {
                throw new \Exception('Course is not active');
            }

            // Create enrollment
            $enrollment = Enrollment::create([
                'user_id' => $registration->user_id,
                'course_id' => $registration->course_id,
                'registration_id' => $registration->id,
                'status' => 'active',
                'started_at' => now(),
                'progress_percentage' => 0.0
            ]);

            // Initialize module progress
            $this->initializeModuleProgress($enrollment);

            DB::commit();
            return $enrollment;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create enrollment after payment', [
                'error' => $e->getMessage(),
                'registration_id' => $registration->id
            ]);
            throw $e;
        }
    }

    private function initializeModuleProgress(Enrollment $enrollment)
    {
        // Get all modules with their contents
        $modules = $enrollment->course->modules()
            ->orderBy('order')
            ->with('contents')
            ->get();

        $now = now();

        foreach ($modules as $index => $module) {
            // Create module progress
            $moduleProgress = ModuleProgress::create([
                'enrollment_id' => $enrollment->id,
                'module_id' => $module->id,
                'status' => $index === 0 ? 'active' : 'locked',
                'progress_percentage' => 0.0,
                'started_at' => $index === 0 ? $now : null
            ]);

            // Create content progress records
            foreach ($module->contents as $content) {
                ContentProgress::create([
                    'enrollment_id' => $enrollment->id,
                    'module_content_id' => $content->id,
                    'status' => 'not_started',
                    'score' => null,
                    'attempts' => 0
                ]);
            }
        }
    }
}
