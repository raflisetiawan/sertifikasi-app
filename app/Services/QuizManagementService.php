<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\ModuleContent;
use Illuminate\Support\Facades\DB;
use Exception;

class QuizManagementService
{
    public function getAllQuizzes()
    {
        return Quiz::with('moduleContent')->orderBy('created_at', 'desc')->get();
    }

    public function createQuiz(array $data): Quiz
    {
        return DB::transaction(function () use ($data) {
            $quiz = Quiz::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'time_limit_minutes' => $data['time_limit_minutes'],
                'passing_score' => $data['passing_score'],
                'max_attempts' => $data['max_attempts'],
                'questions' => $data['questions']
            ]);

            ModuleContent::create([
                'module_id' => $data['module_id'],
                'title' => $data['title'],
                'content_type' => 'quiz',
                'content_id' => $quiz->id,
                'order' => $data['order'],
                'is_required' => $data['is_required'] ?? true
            ]);

            return $quiz->load('moduleContent');
        });
    }

    public function getQuizById(int $id): Quiz
    {
        return Quiz::with('moduleContent')->findOrFail($id);
    }

    public function updateQuiz(Quiz $quiz, array $data): Quiz
    {
        return DB::transaction(function () use ($quiz, $data) {
            $quiz->update($data);

            $moduleContentData = [];
            if (isset($data['title'])) {
                $moduleContentData['title'] = $data['title'];
            }
            if (isset($data['order'])) {
                $moduleContentData['order'] = $data['order'];
            }
            if (isset($data['is_required'])) {
                $moduleContentData['is_required'] = $data['is_required'];
            }

            if (!empty($moduleContentData)) {
                $quiz->moduleContent()->update($moduleContentData);
            }

            return $quiz->load('moduleContent');
        });
    }

    public function deleteQuiz(Quiz $quiz): void
    {
        DB::transaction(function () use ($quiz) {
            $quiz->moduleContent()->delete();
            $quiz->delete();
        });
    }
}
