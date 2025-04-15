<?php

namespace Database\Seeders;

use App\Models\Text;
use App\Models\Quiz;
use App\Models\Video;
use App\Models\ModuleContent;
use Illuminate\Database\Seeder;

class ModuleContentSeeder extends Seeder
{
    public function run()
    {
        // Create text content
        $text = Text::create([
            'title' => 'Introduction to Laravel',
            'content' => 'Laravel is a web application framework with expressive, elegant syntax...',
            'format' => 'markdown'
        ]);

        ModuleContent::create([
            'module_id' => 1,
            'title' => $text->title,
            'content_type' => 'text',
            'content_id' => $text->id,
            'order' => 1,
            'is_required' => true
        ]);

        // Create video content
        $video = Video::create([
            'title' => 'Laravel Installation Guide',
            'description' => 'Step by step guide to install Laravel',
            'video_url' => 'https://youtube.com/watch?v=example',
            'provider' => 'youtube',
            'video_id' => 'example123',
            'duration_seconds' => 600
        ]);

        ModuleContent::create([
            'module_id' => 1,
            'title' => $video->title,
            'content_type' => 'video',
            'content_id' => $video->id,
            'order' => 2,
            'is_required' => true,
            'minimum_duration_seconds' => 540 // 90% of video duration
        ]);

        // Create quiz content
        $quiz = Quiz::create([
            'title' => 'Laravel Basics Quiz',
            'description' => 'Test your understanding of Laravel basics',
            'time_limit_minutes' => 30,
            'passing_score' => 70,
            'max_attempts' => 3,
            'questions' => [
                [
                    'question' => 'What is Laravel?',
                    'type' => 'multiple_choice',
                    'options' => [
                        'A PHP Framework',
                        'A JavaScript Framework',
                        'A Database',
                        'An Operating System'
                    ],
                    'correct_answer' => 0,
                    'score' => 10
                ]
            ]
        ]);

        ModuleContent::create([
            'module_id' => 1,
            'title' => $quiz->title,
            'content_type' => 'quiz',
            'content_id' => $quiz->id,
            'order' => 3,
            'is_required' => true
        ]);
    }
}
