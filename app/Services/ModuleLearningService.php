<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\ContentProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class ModuleLearningService
{
    public function updateModuleProgress(Enrollment $enrollment, Module $module): ContentProgress|null
    {
        $totalContents = $module->contents()->count();
        $completedContents = $enrollment->contentProgresses()
            ->whereIn('module_content_id', $module->contents->pluck('id'))
            ->where('status', 'completed')
            ->count();

        $progressPercentage = $totalContents > 0
            ? ($completedContents / $totalContents) * 100
            : 0;

        $moduleProgress = $enrollment->moduleProgresses()
            ->firstOrCreate(
                ['module_id' => $module->id],
                [
                    'status' => 'in_progress',
                    'progress_percentage' => 0,
                    'started_at' => now()
                ]
            );

        $moduleProgress->update([
            'progress_percentage' => $progressPercentage,
            'status' => $progressPercentage >= 100 ? 'completed' : 'in_progress',
            'completed_at' => $progressPercentage >= 100 ? now() : null
        ]);

        if ($progressPercentage >= 100) {
            $this->unlockNextModule($enrollment, $module);
            $this->updateEnrollmentProgress($enrollment);
        }
        
        return null;
    }

    public function updateEnrollmentProgress(Enrollment $enrollment): void
    {
        $totalModules = $enrollment->course->modules()->count();
        $completedModules = $enrollment->moduleProgresses()
            ->where('status', 'completed')
            ->count();

        $overallProgress = $totalModules > 0
            ? ($completedModules / $totalModules) * 100
            : 0;

        $enrollment->update([
            'progress_percentage' => $overallProgress,
            'status' => $overallProgress >= 100 ? 'completed' : 'active',
            'completed_at' => $overallProgress >= 100 ? now() : null
        ]);
    }

    private function unlockNextModule(Enrollment $enrollment, Module $module): void
    {
        $nextModule = Module::where('course_id', $module->course_id)
            ->where('order', '>', $module->order)
            ->orderBy('order')
            ->first();

        if ($nextModule) {
            $enrollment->moduleProgresses()->updateOrCreate(
                ['module_id' => $nextModule->id],
                [
                    'status' => 'active',
                    'progress_percentage' => 0,
                    'started_at' => now()
                ]
            );
        }
    }

    public function startQuiz(Enrollment $enrollment, Module $module, ModuleContent $content)
    {
        if ($content->module_id !== $module->id || $content->content_type !== 'quiz') {
            throw new \Exception('Invalid quiz content');
        }

        $quiz = $content->content;
        if (!$quiz) {
            throw new \Exception('Quiz not found');
        }

        $attemptsCount = $enrollment->contentProgresses()
            ->where('module_content_id', $content->id)
            ->where('status', 'completed')
            ->count();

        if ($attemptsCount >= $quiz->max_attempts) {
            throw new \Exception('Maximum number of attempts reached');
        }

        $quiz->attempts_left = $quiz->max_attempts - $attemptsCount;

        $contentProgress = $enrollment->contentProgresses()
            ->updateOrCreate(
                ['module_content_id' => $content->id],
                [
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'attempts' => $attemptsCount + 1
                ]
            );

        return $quiz;
    }

    public function handleQuizSubmission(Enrollment $enrollment, ModuleContent $content, array $answers, int $timeSpent): array
    {
        $quiz = $content->content;

        $contentProgress = $enrollment->contentProgresses()
            ->where('module_content_id', $content->id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        if ($quiz->time_limit_minutes > 0 && now()->gt($contentProgress->started_at->addMinutes($quiz->time_limit_minutes))) {
            throw new \Exception('Time limit exceeded');
        }

        $score = $this->calculateQuizScore($quiz, $answers);

        $contentProgress->update([
            'status' => 'completed',
            'score' => $score,
            'completed_at' => now(),
            'last_attempt_at' => now(),
            'submission_details' => [
                'answers' => $answers,
                'time_spent_seconds' => $timeSpent
            ]
        ]);

        $this->updateModuleProgress($enrollment, $content->module);

        return [
            'score' => $score,
            'passing_score' => $quiz->passing_score,
            'passed' => $score >= $quiz->passing_score,
            'attempts_left' => $quiz->max_attempts - $contentProgress->attempts
        ];
    }

    private function calculateQuizScore($quiz, array $answers): float
    {
        $totalScore = 0;
        $earnedScore = 0;

        foreach ($answers as $answer) {
            $question = collect($quiz->questions)->firstWhere('id', $answer['question_id']);
            if (!$question) continue;

            $totalScore += $question['score'];
            if ($this->isAnswerCorrect($question, $answer['answer'])) {
                $earnedScore += $question['score'];
            }
        }

        return $totalScore > 0 ? ($earnedScore / $totalScore) * 100 : 0;
    }

    private function isAnswerCorrect(array $question, $submittedAnswer): bool
    {
        switch ($question['type']) {
            case 'multiple_choice':
            case 'true_false':
                return $submittedAnswer === $question['correct_answer'];
            case 'multiple_answers':
                $submitted = collect($submittedAnswer);
                $correct = collect($question['correct_answers']);
                return $submitted->sort()->values()->all() == $correct->sort()->values()->all();
            default:
                return false;
        }
    }

    public function handleAssignmentSubmission(Enrollment $enrollment, ModuleContent $content, UploadedFile $file, ?string $notes): ContentProgress
    {
        $assignment = $content->content;

        if ($assignment->due_date && now()->gt($assignment->due_date)) {
            throw new \Exception('Assignment submission deadline has passed');
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('assignments/submissions', $filename, 'public');

        $contentProgress = $enrollment->contentProgresses()->updateOrCreate(
            ['module_content_id' => $content->id],
            [
                'status' => 'completed',
                'completed_at' => now(),
                'submission_details' => [
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'notes' => $notes,
                    'submitted_at' => now()->toDateTimeString(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]
            ]
        );

        $this->updateModuleProgress($enrollment, $content->module);

        return $contentProgress;
    }

    public function handlePracticeSubmission(Enrollment $enrollment, ModuleContent $content, array $answers): array
    {
        $practice = $content->content;

        if (!$practice || $content->content_type !== 'practice') {
            throw new \Exception('Invalid practice content');
        }

        $results = $this->calculatePracticeResults($practice->questions, $answers);

        $progress = ContentProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'module_content_id' => $content->id
            ],
            [
                'status' => 'completed',
                'score' => $results['score'],
                'answers' => $answers,
                'completed_at' => now(),
                'submission_details' => [
                    'answers' => $answers,
                    'feedback' => $results['feedback'],
                    'submitted_at' => now()->toDateTimeString()
                ]
            ]
        );

        $this->updateModuleProgress($enrollment, $content->module);

        return [
            'score' => $results['score'],
            'feedback' => $results['feedback'],
            'progress' => $progress
        ];
    }

    public function startPractice(ModuleContent $content)
    {
        if ($content->content_type !== 'practice') {
            throw new \Exception('Content is not a practice');
        }

        $practice = $content->content;

        return $practice->getForStudent();
    }

    private function calculatePracticeResults($questions, $answers)
    {
        $totalQuestions = count($questions);
        $correctAnswers = 0;
        $feedback = [];

        foreach ($questions as $index => $question) {
            $userAnswer = collect($answers)->firstWhere('question_id', $index + 1);

            if (!$userAnswer) continue;

            $isCorrect = $this->checkAnswer($userAnswer['answer'], $question['answer_key']);

            if ($isCorrect) {
                $correctAnswers++;
            }

            $feedback[] = [
                'question' => $question['question'],
                'is_correct' => $isCorrect,
                'explanation' => $question['explanation'] ?? null,
                'correct_answer' => $question['answer_key'],
                'your_answer' => $userAnswer['answer']
            ];
        }

        $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

        return [
            'score' => round($score, 2),
            'feedback' => $feedback
        ];
    }

    private function checkAnswer($userAnswer, $correctAnswer)
    {
        if (is_array($correctAnswer)) {
            $userArr = is_array($userAnswer) ? $userAnswer : [$userAnswer];
            sort($userArr);
            sort($correctAnswer);
            return $userArr == $correctAnswer;
        }

        return strtolower(trim((string)$userAnswer)) === strtolower(trim((string)$correctAnswer));
    }
}
