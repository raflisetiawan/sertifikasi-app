<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CourseEnrollmentService
{
    public function getEnrolledCourses(Request $request): LengthAwarePaginator|Collection
    {
        $query = Registration::with(['course', 'payment', 'enrollment.moduleProgresses'])
            ->where('user_id', Auth::id());

        if ($request->sort === 'latest') {
            $query->orderBy('created_at', 'desc');
        }

        return $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();
    }

    public function getEnrolledCourseDetails(int $courseId): Registration
    {
        return Registration::with([
            'course' => function ($query) {
                $query->with(['modules' => function ($q) {
                    $q->orderBy('order');
                }]);
            },
            'payment',
            'enrollment.moduleProgresses'
        ])->where('user_id', Auth::id())
          ->where('course_id', $courseId)
          ->firstOrFail();
    }

    public function getModuleAccessStatus(Module $module): string
    {
        if (!$module->is_access_restricted) {
            return 'available';
        }

        $now = now();

        if ($module->access_start_at && $now->lt($module->access_start_at)) {
            return 'upcoming';
        }

        if ($module->access_end_at && $now->gt($module->access_end_at)) {
            return 'expired';
        }

        if ($module->isAccessibleNow()) {
            return 'available';
        }

        return 'locked';
    }
}
