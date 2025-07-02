<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleLearningResource extends JsonResource
{
    public function toArray($request)
    {
        $module = $this['module'];
        $moduleProgress = $this['module_progress'];
        $contentProgress = $this['content_progress'];

        return [
            'success' => true,
            'data' => [
                'module' => [
                    'id' => $module->id,
                    'title' => $module->title,
                    'subtitle' => $module->subtitle,
                    'description' => $module->description,
                    'type' => $module->type,
                    'estimated_time_min' => $module->estimated_time_min,
                    'order' => $module->order,
                    'thumbnail_url' => $module->thumbnail_url,
                ],
                'progress' => $moduleProgress ? [
                    'status' => $moduleProgress->status,
                    'progress_percentage' => $moduleProgress->progress_percentage,
                    'started_at' => $moduleProgress->started_at,
                    'completed_at' => $moduleProgress->completed_at
                ] : null,
                'contents' => $module->contents->map(function ($content) use ($contentProgress) {
                    $progress = $contentProgress[$content->id] ?? null;

                    return [
                        'id' => $content->id,
                        'title' => $content->title,
                        'type' => $content->content_type,
                        'order' => $content->order,
                        'is_required' => $content->is_required,
                        'minimum_duration_seconds' => $content->minimum_duration_seconds,
                        'content' => $this->transformContent($content),
                        'progress' => $progress ? [
                            'status' => $progress->status,
                            'score' => $progress->score,
                            'completed_at' => $progress->completed_at,
                            'last_accessed_at' => $progress->updated_at
                        ] : null
                    ];
                })
            ]
        ];
    }

    private function transformContent($content)
    {
        switch ($content->content_type) {
            case 'video':
                return [
                    'video_url' => $content->content->video_url,
                    'duration_seconds' => $content->content->duration_seconds,
                    'thumbnail_url' => $content->content->thumbnail_url,
                    'is_downloadable' => $content->content->is_downloadable,
                ];
            case 'text':
                return [
                    'content' => $content->content->content,
                    'format' => $content->content->format
                ];
            case 'quiz':
                return [
                    'time_limit_minutes' => $content->content->time_limit_minutes,
                    'passing_score' => $content->content->passing_score,
                    'max_attempts' => $content->content->max_attempts,
                ];
            case 'assignment':
                return [
                    'instructions' => $content->content->instructions,
                    'due_date' => $content->content->due_date,
                    'max_file_size_mb' => $content->content->max_file_size_mb,
                    'submission_requirements' => $content->content->submission_requirements,
                ];
            case 'file':
                return [
                    'file_url' => $content->content->file_url,
                    'mime_type' => $content->content->mime_type,
                    'file_size' => $content->content->file_size,
                    'title' => $content->content->title,
                    'description' => $content->content->description,
                ];
            case 'practice':
                return [
                    'title' => $content->content->title,
                    'description' => $content->content->description,
                    'time_limit_minutes' => $content->content->time_limit_minutes,
                    'questions' => collect($content->content->questions)->map(function ($question) {
                        return [
                            'question' => $question['question'],
                            'type' => $question['type'],
                            'options' => $question['options'] ?? [],
                        ];
                    })->values()->all()
                ];
            default:
                return null;
        }
    }
}
