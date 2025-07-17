<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Assignment;
use App\Models\Post;
use App\Models\Thread;
use App\Models\Trainer;
use App\Models\ContentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrainerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $trainer = Trainer::where('user_id', $user->id)->first();

        if (!$trainer) {
            return response()->json(['message' => 'Trainer profile not found.'], 404);
        }

        $trainerCourses = $trainer->courses()->with('enrollments')->get();

        // Card Statistics
        $totalActiveClasses = $trainerCourses->where('status', 'active')->count();

        $totalStudents = 0;
        foreach ($trainerCourses as $course) {
            $totalStudents += $course->enrollments->count();
        }

        // Assuming assignments are linked via module content and content progress
        $assignmentsToGrade = ContentProgress::whereHas('moduleContent.module.course.trainers', function ($query) use ($trainer) {
            $query->where('trainer_id', $trainer->id);
        })
        ->whereHas('moduleContent', function ($query) {
            $query->where('content_type', 'assignment');
        })
        ->whereNotNull('submission_details') // Assuming submission_details indicates a submitted assignment
        ->whereNull('feedback') // Assuming null feedback means not graded yet
        ->count();

        // Assuming unread forum posts/questions are those without replies from the trainer
        $unreadForumPosts = Post::whereHas('thread.forum.course.trainers', function ($query) use ($trainer) {
            $query->where('trainer_id', $trainer->id);
        })
        ->where('user_id', '!=', $user->id) // Not posts by the trainer themselves
        ->whereDoesntHave('thread.posts', function ($query) use ($user) {
            $query->where('user_id', $user->id); // No posts by the current trainer in this thread
        })
        ->count();

        // Action Required (example: top 5 assignments to grade, top 3 unread forum questions)
        $assignmentsNeedingGrading = ContentProgress::whereHas('moduleContent.module.course.trainers', function ($query) use ($trainer) {
            $query->where('trainer_id', $trainer->id);
        })
        ->whereHas('moduleContent', function ($query) {
            $query->where('content_type', 'assignment');
        })
        ->whereNotNull('submission_details')
        ->whereNull('feedback')
        ->with('moduleContent.module.course')
        ->take(5)
        ->get()
        ->map(function ($progress) {
            return [
                'assignment_title' => $progress->moduleContent->title,
                'course_name' => $progress->moduleContent->module->course->name,
                'module_name' => $progress->moduleContent->module->title,
            ];
        });

        $newForumQuestions = Thread::whereHas('forum.course.trainers', function ($query) use ($trainer) {
            $query->where('trainer_id', $trainer->id);
        })
        ->where('user_id', '!=', $user->id) // Not threads started by the trainer themselves
        ->whereDoesntHave('posts', function ($query) use ($user) {
            $query->where('user_id', $user->id); // No posts by the current trainer in this thread
        })
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get()
        ->map(function ($thread) {
            return [
                'thread_title' => $thread->title,
                'course_name' => $thread->forum->course->name,
            ];
        });

        // Class Activity Summary (last 5 active classes with student count and average progress)
        $classActivitySummary = $trainerCourses->where('status', 'active')
            ->sortByDesc('updated_at')
            ->take(5)
            ->map(function ($course) {
                $totalEnrollments = $course->enrollments->count();
                $averageProgress = $course->enrollments->avg('progress_percentage'); // Assuming progress_percentage exists on Enrollment model

                return [
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'student_count' => $totalEnrollments,
                    'average_progress' => round($averageProgress, 2) . '%',
                ];
            });

        return response()->json([
            'statistics' => [
                'total_active_classes' => $totalActiveClasses,
                'total_students' => $totalStudents,
                'assignments_to_grade_count' => $assignmentsToGrade,
                'unread_forum_posts_count' => $unreadForumPosts,
            ],
            'action_required' => [
                'assignments_needing_grading' => $assignmentsNeedingGrading,
                'new_forum_questions' => $newForumQuestions,
            ],
            'class_activity_summary' => $classActivitySummary,
            'quick_links' => [
                ['name' => 'Kelola Kelas Saya', 'url' => '/trainer/my-classes'],
                ['name' => 'Forum Diskusi', 'url' => '/trainer/forums'],
                ['name' => 'Lihat Penilaian Siswa', 'url' => '/trainer/student-grades'],
            ],
        ]);
    }
}
