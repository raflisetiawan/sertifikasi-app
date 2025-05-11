<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignmentSubmissionRequest;
use App\Http\Requests\QuizSubmissionRequest;
use App\Http\Resources\ModuleLearningResource;
use App\Http\Resources\QuizStartResource;
use App\Models\ContentProgress;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ModuleLearningController extends Controller
{
    public function show(Enrollment $enrollment, Module $module)
    {
        // Check if user is enrolled
        if ($enrollment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        // Check if module belongs to the enrolled course
        if ($module->course_id !== $enrollment->course_id) {
            return response()->json([
                'success' => false,
                'message' => 'This module is not part of the enrolled course'
            ], 403);
        }

        // Get module progress
        $moduleProgress = $enrollment->moduleProgresses()
            ->where('module_id', $module->id)
            ->first();

        // Get content progress
        $contentProgress = $enrollment->contentProgresses()
            ->whereIn('module_content_id', $module->contents->pluck('id'))
            ->get()
            ->keyBy('module_content_id');

        // Load relationships
        $module->load(['contents' => function ($query) {
            $query->orderBy('order')->with('content');
        }]);

        return new ModuleLearningResource([
            'module' => $module,
            'module_progress' => $moduleProgress,
            'content_progress' => $contentProgress
        ]);
    }

    public function updateProgress(Request $request, Enrollment $enrollment, Module $module)
    {
        // Validate user access
        if ($enrollment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        // Validate module belongs to course
        if ($module->course_id !== $enrollment->course_id) {
            return response()->json([
                'success' => false,
                'message' => 'This module is not part of the enrolled course'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Get or create module progress
            $moduleProgress = $enrollment->moduleProgresses()
                ->firstOrCreate(
                    ['module_id' => $module->id],
                    [
                        'status' => 'in_progress',
                        'progress_percentage' => 0,
                        'started_at' => now()
                    ]
                );

            // Update content progress if provided
            if ($request->has('content_id')) {
                $content = $module->contents()->findOrFail($request->content_id);

                $contentProgress = $enrollment->contentProgresses()
                    ->updateOrCreate(
                        ['module_content_id' => $content->id],
                        [
                            'status' => $request->content_status ?? 'in_progress',
                            'score' => $request->score,
                            'completed_at' => $request->completed ? now() : null
                        ]
                    );
            }

            // Calculate overall module progress
            $totalContents = $module->contents()->count();
            $completedContents = $enrollment->contentProgresses()
                ->whereIn('module_content_id', $module->contents->pluck('id'))
                ->where('status', 'completed')
                ->count();

            $progressPercentage = $totalContents > 0
                ? ($completedContents / $totalContents) * 100
                : 0;

            // Update module progress
            $moduleProgress->update([
                'progress_percentage' => $progressPercentage,
                'status' => $progressPercentage >= 100 ? 'completed' : 'active',
                'completed_at' => $progressPercentage >= 100 ? now() : null
            ]);

            // Update enrollment progress
            $this->updateEnrollmentProgress($enrollment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'data' => [
                    'module_progress' => $moduleProgress,
                    'content_progress' => $contentProgress ?? null,
                    'enrollment_progress' => $enrollment->fresh()
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function updateEnrollmentProgress(Enrollment $enrollment)
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

    public function startQuiz(Enrollment $enrollment, Module $module, ModuleContent $content)
    {
        // Validate user access
        if ($enrollment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        // Validate module belongs to course
        if ($module->course_id !== $enrollment->course_id) {
            return response()->json([
                'success' => false,
                'message' => 'This module is not part of the enrolled course'
            ], 403);
        }

        // Validate content belongs to module and is a quiz
        if ($content->module_id !== $module->id || $content->content_type !== 'quiz') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid quiz content'
            ], 404);
        }

        // Get quiz instance
        $quiz = $content->content;
        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz not found'
            ], 404);
        }

        // Check attempts
        $attemptsCount = $enrollment->contentProgresses()
            ->where('module_content_id', $content->id)
            ->where('status', 'completed')
            ->count();

        if ($attemptsCount >= $quiz->max_attempts) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum number of attempts reached'
            ], 403);
        }

        // Add attempts left to quiz object
        $quiz->attempts_left = $quiz->max_attempts - $attemptsCount;

        // Create or update progress record for starting the quiz
        $contentProgress = $enrollment->contentProgresses()
            ->updateOrCreate(
                ['module_content_id' => $content->id],
                [
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'attempts' => $attemptsCount + 1
                ]
            );

        return new QuizStartResource($quiz);
    }

    public function submitQuiz(QuizSubmissionRequest $request, Enrollment $enrollment, Module $module, ModuleContent $content)
    {
        // Validate user access
        if ($enrollment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        // Validate content type and ownership
        if ($content->module_id !== $module->id || $content->content_type !== 'quiz') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid quiz content'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Get quiz and progress
            $quiz = $content->content;
            $contentProgress = $enrollment->contentProgresses()
                ->where('module_content_id', $content->id)
                ->where('status', 'in_progress')
                ->firstOrCreate(
                    ['module_content_id' => $content->id],
                    [
                        'status' => 'in_progress',
                        'started_at' => now()
                    ]
                );

            // Validate time limit if set
            if ($quiz->time_limit_minutes > 0) {
                $timeLimit = $contentProgress->started_at->addMinutes($quiz->time_limit_minutes);
                if (now()->gt($timeLimit)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Time limit exceeded'
                    ], 403);
                }
            }

            // Calculate score
            $score = $this->calculateQuizScore($quiz, $request->answers);

            // Update progress
            $contentProgress->update([
                'status' => 'completed',
                'score' => $score,
                'completed_at' => now(),
                'last_attempt_at' => now(),
                'submission_details' => [
                    'answers' => $request->answers,
                    'time_spent_seconds' => $request->time_spent_seconds
                ]
            ]);

            // Update module progress
            $this->updateModuleProgress($enrollment, $module);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'score' => $score,
                    'passing_score' => $quiz->passing_score,
                    'passed' => $score >= $quiz->passing_score,
                    'attempts_left' => $quiz->max_attempts - $contentProgress->attempts
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quiz submission failed:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'quiz_id' => $content->content_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateQuizScore($quiz, $answers)
    {
        $totalScore = 0;
        $earnedScore = 0;

        foreach ($answers as $answer) {
            $question = collect($quiz->questions)
                ->firstWhere('id', $answer['question_id']);

            if (!$question) continue;

            // Add to total possible score
            $totalScore += $question['score'];

            // Check if answer is correct based on question type
            switch ($question['type']) {
                case 'multiple_choice':
                    if ($answer['answer'] === $question['correct_answer']) {
                        $earnedScore += $question['score'];
                    }
                    break;

                case 'multiple_answers':
                    $submitted = collect($answer['answer']);
                    $correct = collect($question['correct_answers']);
                    if ($submitted->sort()->values() == $correct->sort()->values()) {
                        $earnedScore += $question['score'];
                    }
                    break;

                case 'true_false':
                    if ($answer['answer'] === $question['correct_answer']) {
                        $earnedScore += $question['score'];
                    }
                    break;
            }
        }

        // Calculate percentage
        return $totalScore > 0 ? ($earnedScore / $totalScore) * 100 : 0;
    }

    private function generateQuizFeedback($quiz, $answers)
    {
        $feedback = [];

        foreach ($answers as $answer) {
            $question = collect($quiz->questions)
                ->firstWhere('id', $answer['question_id']);

            if (!$question) continue;

            $isCorrect = false;
            switch ($question['type']) {
                case 'multiple_choice':
                case 'true_false':
                    $isCorrect = $answer['answer'] === $question['correct_answer'];
                    break;

                case 'multiple_answers':
                    $submitted = collect($answer['answer']);
                    $correct = collect($question['correct_answers']);
                    $isCorrect = $submitted->sort()->values() == $correct->sort()->values();
                    break;
            }

            $feedback[] = [
                'question_id' => $question['id'],
                'correct' => $isCorrect,
                'explanation' => $isCorrect ? null : $question['explanation']
            ];
        }

        return $feedback;
    }

    private function updateModuleProgress(Enrollment $enrollment, Module $module)
    {
        // Calculate total module contents
        $totalContents = $module->contents()->count();

        // Count completed contents
        $completedContents = $enrollment->contentProgresses()
            ->whereIn('module_content_id', $module->contents->pluck('id'))
            ->where('status', 'completed')
            ->count();

        // Calculate progress percentage
        $progressPercentage = $totalContents > 0
            ? ($completedContents / $totalContents) * 100
            : 0;

        // Get or create current module progress
        $moduleProgress = $enrollment->moduleProgresses()
            ->firstOrCreate(
                ['module_id' => $module->id],
                [
                    'status' => 'active', // First module starts as active
                    'progress_percentage' => 0,
                    'started_at' => now()
                ]
            );

        // Update current module progress
        $moduleProgress->update([
            'progress_percentage' => $progressPercentage,
            'status' => $progressPercentage >= 100 ? 'completed' : 'active',
            'completed_at' => $progressPercentage >= 100 ? now() : null
        ]);

        // If current module is completed, unlock next module
        if ($progressPercentage >= 100) {
            // Find next module in course
            $nextModule = Module::where('course_id', $module->course_id)
                ->where('order', '>', $module->order)
                ->orderBy('order')
                ->first();

            if ($nextModule) {
                // Create or update next module's progress
                $enrollment->moduleProgresses()
                    ->updateOrCreate(
                        ['module_id' => $nextModule->id],
                        [
                            'status' => 'active', // Unlock next module
                            'progress_percentage' => 0,
                            'started_at' => now()
                        ]
                    );
            }
        }

        return $moduleProgress;
    }

    public function submitAssignment(AssignmentSubmissionRequest $request, Enrollment $enrollment, Module $module, ModuleContent $content)
    {
        // Validate user access
        if ($enrollment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        // Validate content type and ownership
        if ($content->module_id !== $module->id || $content->content_type !== 'assignment') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid assignment content'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Get assignment instance
            $assignment = $content->content;
            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment not found'
                ], 404);
            }

            // Check if due date has passed
            if ($assignment->due_date && now()->gt($assignment->due_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment submission deadline has passed'
                ], 403);
            }

            // Store the submission file
            $file = $request->file('submission_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs(
                'assignments/submissions',
                $filename,
                'public'
            );

            // Create or update submission progress
            $contentProgress = $enrollment->contentProgresses()
                ->updateOrCreate(
                    ['module_content_id' => $content->id],
                    [
                        'status' => 'completed',
                        'completed_at' => now(),
                        'submission_details' => [
                            'file_path' => $path,
                            'original_filename' => $file->getClientOriginalName(),
                            'notes' => $request->notes,
                            'submitted_at' => now()->toDateTimeString(),
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType()
                        ]
                    ]
                );

            // Update module progress
            $this->updateModuleProgress($enrollment, $module);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment submitted successfully',
                'data' => [
                    'content_progress' => $contentProgress,
                    'submission_details' => $contentProgress->submission_details
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Assignment submission failed:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'assignment_id' => $content->content_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function startPractice($enrollmentId, $moduleId, $contentId)
    {
        $content = ModuleContent::findOrFail($contentId);

        if ($content->content_type !== 'practice') {
            return response()->json([
                'success' => false,
                'message' => 'Content is not a practice'
            ], 400);
        }

        $practice = $content->content;

        return response()->json([
            'success' => true,
            'data' => $practice->getForStudent()
        ]);
    }

    public function submitPractice(Request $request, $enrollmentId, $moduleId, $contentId)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $content = ModuleContent::findOrFail($contentId);
            $practice = $content->content;

            if (!$practice || $content->content_type !== 'practice') {
                throw new \Exception('Invalid practice content');
            }

            // Calculate results
            $results = $this->calculatePracticeResults($practice->questions, $request->answers);

            // Update progress
            $progress = ContentProgress::updateOrCreate(
                [
                    'enrollment_id' => $enrollmentId,
                    'module_content_id' => $contentId
                ],
                [
                    'status' => 'completed',
                    'score' => $results['score'],
                    'answers' => $request->answers,
                    'completed_at' => now(),
                    'submission_details' => [
                        'answers' => $request->answers,
                        'feedback' => $results['feedback'],
                        'submitted_at' => now()->toDateTimeString()
                    ]
                ]
            );

            // Update module progress
            $this->updateModuleProgress(
                Enrollment::findOrFail($enrollmentId),
                Module::findOrFail($moduleId)
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Practice submitted successfully',
                'data' => [
                    'score' => $results['score'],
                    'feedback' => $results['feedback'],
                    'progress' => $progress
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit practice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculatePracticeResults($questions, $answers)
    {
        $totalQuestions = count($questions);
        $correctAnswers = 0;
        $feedback = [];

        foreach ($questions as $index => $question) {
            // Find matching answer using the index instead of id
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

        // Convert both to strings and compare
        return strtolower(trim((string)$userAnswer)) === strtolower(trim((string)$correctAnswer));
    }
}
