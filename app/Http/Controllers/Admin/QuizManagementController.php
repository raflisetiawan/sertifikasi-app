<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Services\QuizManagementService;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use Illuminate\Http\Request;

class QuizManagementController extends Controller
{
    protected $quizManagementService;

    public function __construct(QuizManagementService $quizManagementService)
    {
        $this->quizManagementService = $quizManagementService;
    }

    /**
     * Display a listing of quizzes
     */
    public function index()
    {
        $quizzes = $this->quizManagementService->getAllQuizzes();
        return response()->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }

    /**
     * Store a newly created quiz
     */
    public function store(StoreQuizRequest $request)
    {
        try {
            $quiz = $this->quizManagementService->createQuiz($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Kuis berhasil ditambahkan',
                'data' => $quiz
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kuis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified quiz
     */
    public function show($id)
    {
        try {
            $quiz = $this->quizManagementService->getQuizById($id);
            return response()->json([
                'success' => true,
                'data' => $quiz
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kuis tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified quiz
     */
    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        try {
            $quiz = $this->quizManagementService->updateQuiz($quiz, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Kuis berhasil diperbarui',
                'data' => $quiz
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kuis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified quiz
     */
    public function destroy(Quiz $quiz)
    {
        try {
            $this->quizManagementService->deleteQuiz($quiz);
            return response()->json([
                'success' => true,
                'message' => 'Kuis berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kuis',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}