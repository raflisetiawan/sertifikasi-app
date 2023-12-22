<?php

namespace App\Jobs;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCourseStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $courses = Course::where('status', '!=', 'completed')->get();

        foreach ($courses as $course) {
            $now = now();
            $operationalStart = $course->operational_start;
            $operationalEnd = $course->operational_end;

            if ($now >= $operationalStart && $now <= $operationalEnd) {
                $course->status = 'ongoing';
            } elseif ($now > $operationalEnd) {
                $course->status = 'completed';
            } else {
                $course->status = 'not_started';
            }

            $course->save();
        }
    }
}
