<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ContentProgress;
use Illuminate\Http\Request;

class TrainerModuleController extends Controller
{
    public function show(Module $module)
    {
        $module->load(['course', 'contents.content']);

        if (!$module) {
            return response()->json(['message' => 'Module not found.'], 404);
        }

        $contentsData = $module->contents->map(function ($content) {
            $contentArray = [
                'id' => $content->id,
                'title' => $content->title,
                'type' => $content->content_type,
                'order' => $content->order,
            ];

            // Add type-specific properties
            switch ($content->content_type) {
                case 'video':
                    $contentArray['url'] = $content->content->video_url ?? null;
                    $contentArray['duration_minutes'] = $content->content->duration_seconds ? round($content->content->duration_seconds / 60) : null;
                    break;
                case 'text':
                    $contentArray['content_html'] = $content->content->content ?? null; // Assuming content is HTML or Markdown that can be rendered
                    break;
                case 'file':
                    $contentArray['file_url'] = $content->content->file_path ? asset('storage/' . $content->content->file_path) : null;
                    $contentArray['file_name'] = $content->content->file_name ?? null;
                    break;
                case 'quiz':
                    $contentArray['question_count'] = count($content->content->questions ?? []);
                    // Trainer stats for Quiz
                    $totalAttempts = ContentProgress::where('module_content_id', $content->id)->count();
                    $averageScore = ContentProgress::where('module_content_id', $content->id)->avg('score');
                    $contentArray['trainer_stats'] = [
                        'total_attempts' => $totalAttempts,
                        'average_score' => $averageScore ? round($averageScore, 2) . '%' : 'N/A',
                        'needs_review_count' => 0, // Assuming no manual review for quizzes unless specified
                    ];
                    break;
                case 'assignment':
                    $contentArray['description'] = $content->content->description ?? null;
                    // Trainer stats for Assignment
                    $totalSubmissions = ContentProgress::where('module_content_id', $content->id)->whereNotNull('submission_details')->count();
                    $pendingGradingCount = ContentProgress::where('module_content_id', $content->id)->whereNotNull('submission_details')->whereNull('feedback')->count();
                    $gradedCount = ContentProgress::where('module_content_id', $content->id)->whereNotNull('submission_details')->whereNotNull('feedback')->count();
                    $contentArray['trainer_stats'] = [
                        'total_submissions' => $totalSubmissions,
                        'pending_grading_count' => $pendingGradingCount,
                        'graded_count' => $gradedCount,
                    ];
                    break;
                // Add other content types (e.g., practice) as needed
            }

            return $contentArray;
        });

        return response()->json([
            'module' => [
                'id' => $module->id,
                'title' => $module->title,
                'description' => $module->description,
                'course_id' => $module->course->id ?? null,
                'course_name' => $module->course->name ?? null,
                'order' => $module->order,
                'contents' => $contentsData,
            ]
        ]);
    }
}
