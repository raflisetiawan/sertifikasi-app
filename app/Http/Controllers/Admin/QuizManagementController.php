<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class QuizManagementController extends Controller
{
    /**
     * Display a listing of quizzes
     */
    public function index()
    {
        $quizzes = Quiz::with('moduleContent')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }

    /**
     * Store a newly created quiz
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'time_limit_minutes' => 'required|integer|min:1',
            'passing_score' => 'required|integer|between:0,100',
            'max_attempts' => 'required|integer|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,essay',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array|min:2',
            'questions.*.correct_answer' => 'required_unless:questions.*.type,essay',
            'questions.*.score' => 'required|integer|min:1',
            'order' => 'required|integer|min:0'
        ], [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul kuis wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'description.required' => 'Deskripsi wajib diisi',
            'time_limit_minutes.required' => 'Batas waktu wajib diisi',
            'time_limit_minutes.min' => 'Batas waktu minimal 1 menit',
            'passing_score.required' => 'Nilai kelulusan wajib diisi',
            'passing_score.between' => 'Nilai kelulusan harus antara 0-100',
            'max_attempts.required' => 'Jumlah percobaan wajib diisi',
            'max_attempts.min' => 'Jumlah percobaan minimal 1',
            'questions.required' => 'Pertanyaan wajib diisi',
            'questions.min' => 'Minimal harus ada 1 pertanyaan',
            'questions.*.question.required' => 'Pertanyaan wajib diisi',
            'questions.*.type.required' => 'Tipe pertanyaan wajib diisi',
            'questions.*.type.in' => 'Tipe pertanyaan tidak valid',
            'questions.*.options.required_if' => 'Pilihan jawaban wajib diisi untuk tipe pilihan ganda',
            'questions.*.options.min' => 'Minimal harus ada 2 pilihan jawaban',
            'questions.*.correct_answer.required_unless' => 'Jawaban benar wajib diisi',
            'questions.*.score.required' => 'Skor pertanyaan wajib diisi',
            'questions.*.score.min' => 'Skor minimal 1',
            'order.required' => 'Urutan wajib diisi',
            'order.min' => 'Urutan minimal 0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create quiz
            $quiz = Quiz::create([
                'title' => $request->title,
                'description' => $request->description,
                'time_limit_minutes' => $request->time_limit_minutes,
                'passing_score' => $request->passing_score,
                'max_attempts' => $request->max_attempts,
                'questions' => $request->questions
            ]);

            // Create module content entry
            $moduleContent = ModuleContent::create([
                'module_id' => $request->module_id,
                'title' => $request->title,
                'content_type' => 'quiz',
                'content_id' => $quiz->id,
                'order' => $request->order,
                'is_required' => $request->input('is_required', true)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kuis berhasil ditambahkan',
                'data' => $quiz->load('moduleContent')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
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
        $quiz = Quiz::with('moduleContent')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $quiz
        ]);
    }

    /**
     * Update the specified quiz
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'time_limit_minutes' => 'sometimes|integer|min:1',
            'passing_score' => 'sometimes|integer|between:0,100',
            'max_attempts' => 'sometimes|integer|min:1',
            'questions' => 'sometimes|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,essay',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array|min:2',
            'questions.*.correct_answer' => 'required_unless:questions.*.type,essay',
            'questions.*.score' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $quiz = Quiz::findOrFail($id);
            $quiz->update($validator->validated());

            // Update related module content title if title is changed
            if ($request->has('title')) {
                $quiz->moduleContent()->update(['title' => $request->title]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kuis berhasil diperbarui',
                'data' => $quiz->load('moduleContent')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $quiz = Quiz::findOrFail($id);

            // Delete related module content first
            $quiz->moduleContent()->delete();

            // Delete the quiz
            $quiz->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kuis berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kuis',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
