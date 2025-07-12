<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignmentSubmissionRequest;
use App\Http\Requests\QuizSubmissionRequest;
use App\Http\Requests\UpdateProgressRequest;
use App\Http\Requests\PracticeSubmissionRequest;
use App\Http\Requests\ShowModuleLearningRequest;
use App\Http\Resources\ModuleLearningResource;
use App\Http\Resources\QuizStartResource;
use App\Models\ContentProgress;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Services\ModuleLearningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleLearningController extends Controller
{
    protected $moduleLearningService;

    public function __construct(ModuleLearningService $moduleLearningService)
    {
        $this->moduleLearningService = $moduleLearningService;
    }

    public function show(ShowModuleLearningRequest $request, Enrollment $enrollment, Module $module)
    {
        $moduleProgress = $enrollment->moduleProgresses()
            ->where('module_id', $module->id)
            ->first();

        $contentProgress = $enrollment->contentProgresses()
            ->whereIn('module_content_id', $module->contents->pluck('id'))
            ->get()
            ->keyBy('module_content_id');

        $module->load(['contents' => function ($query) {
            $query->orderBy('order')->with('content');
        }]);

        return new ModuleLearningResource([
            'module' => $module,
            'module_progress' => $moduleProgress,
            'content_progress' => $contentProgress
        ]);
    }

    public function updateProgress(UpdateProgressRequest $request, Enrollment $enrollment, Module $module)
    {
        try {
            DB::beginTransaction();

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

            $this->moduleLearningService->updateModuleProgress($enrollment, $module);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'data' => [
                    'module_progress' => $enrollment->moduleProgresses()->where('module_id', $module->id)->first(),
                    'content_progress' => $contentProgress,
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

    public function startQuiz(Enrollment $enrollment, Module $module, ModuleContent $content)
    {
        try {
            $quiz = $this->moduleLearningService->startQuiz($enrollment, $module, $content);
            return new QuizStartResource($quiz);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function submitQuiz(QuizSubmissionRequest $request, Enrollment $enrollment, Module $module, ModuleContent $content)
    {
        try {
            DB::beginTransaction();

            $result = $this->moduleLearningService->handleQuizSubmission(
                $enrollment,
                $content,
                $request->answers,
                $request->time_spent_seconds
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $result
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

    public function submitAssignment(AssignmentSubmissionRequest $request, Enrollment $enrollment, Module $module, ModuleContent $content)
    {
        Log::debug($request->all());
        try {
            DB::beginTransaction();

            $contentProgress = $this->moduleLearningService->handleAssignmentSubmission(
                $enrollment,
                $content,
                $request->file('submission_file'),
                $request->notes
            );

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

    public function startPractice(Enrollment $enrollment, Module $module, ModuleContent $content)
    {
        try {
            $practiceData = $this->moduleLearningService->startPractice($content);
            return response()->json([
                'success' => true,
                'data' => $practiceData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function submitPractice(PracticeSubmissionRequest $request, Enrollment $enrollment, Module $module, ModuleContent $content)
    {
        try {
            DB::beginTransaction();

            $results = $this->moduleLearningService->handlePracticeSubmission(
                $enrollment,
                $content,
                $request->answers
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Practice submitted successfully',
                'data' => $results
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

    public function getQuizAttempt(Enrollment $enrollment, ModuleContent $content)
    {
        try {
            $quizAttempt = $enrollment->contentProgresses()
                ->where('module_content_id', $content->id)
                ->first();

            if (!$quizAttempt || $quizAttempt->score === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz attempt not found or not completed for this enrollment and content.'
                ], 404);
            }

            $quiz = $content->content;
            $passed = $quizAttempt->score >= $quiz->passing_score;
            $allAttemptsUsed = $quizAttempt->attempts >= $quiz->max_attempts;

            return response()->json([
                'success' => true,
                'data' => [
                    'score' => $quizAttempt->score,
                    'attempts' => $quizAttempt->attempts,
                    'last_attempt_at' => $quizAttempt->last_attempt_at,
                    'max_attempts' => $quiz->max_attempts,
                    'passing_score' => $quiz->passing_score,
                    'passed' => $passed,
                    'all_attempts_used' => $allAttemptsUsed,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz attempt',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
