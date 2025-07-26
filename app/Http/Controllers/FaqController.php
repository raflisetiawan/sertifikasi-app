<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\HelpCenterQuestion;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    /**
     * Display a listing of the FAQs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Faq::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', '%' . $search . '%')
                  ->orWhere('answer', 'like', '%' . $search . '%');
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        $faqs = $query->get();
        return response()->json([
            'success' => true,
            'data' => $faqs->map(function($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                ];
            })
        ]);
    }

    /**
     * Store a newly created FAQ in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'category' => 'nullable|string',
        ]);

        $faq = Faq::create($request->all());

        return response()->json($faq, 201);
    }

    /**
     * Display the specified FAQ.
     *
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Faq $faq)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
            ]
        ]);
    }

    /**
     * Update the specified FAQ in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'category' => 'nullable|string',
        ]);

        $faq->update($request->all());

        return response()->json($faq, 200);
    }

    /**
     * Remove the specified FAQ from storage.
     *
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();

        return response()->json(null, 204);
    }

    /**
     * Peserta mengirim pertanyaan ke pusat bantuan
     */
    public function submitQuestion(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        $question = HelpCenterQuestion::create([
            'user_id' => Auth::id(),
            'question' => $request->question,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan Anda telah dikirim. Kami akan segera merespon.'
        ]);
    }

    /**
     * Peserta melihat riwayat pertanyaan sendiri
     */
    public function myQuestions(Request $request)
    {
        $user = Auth::user();
        $questions = HelpCenterQuestion::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $questions->map(function($q) {
                return [
                    'id' => $q->id,
                    'question' => $q->question,
                    'answer' => $q->answer,
                    'status' => $q->status,
                    'created_at' => $q->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    /**
     * Admin melihat semua pertanyaan peserta (bisa filter status)
     */
    public function allQuestions(Request $request)
    {
        $query = HelpCenterQuestion::query();
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        $questions = $query->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $questions->map(function($q) {
                return [
                    'id' => $q->id,
                    'user_id' => $q->user_id,
                    'question' => $q->question,
                    'answer' => $q->answer,
                    'status' => $q->status,
                    'created_at' => $q->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    /**
     * Admin menjawab pertanyaan peserta
     */
    public function answerQuestion(Request $request, $id)
    {
        $request->validate([
            'answer' => 'required|string',
        ]);
        $question = HelpCenterQuestion::findOrFail($id);
        $question->answer = $request->answer;
        $question->status = 'answered';
        $question->save();

        // (Opsional) Kirim notifikasi/email ke peserta di sini

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $question->id,
                'user_id' => $question->user_id,
                'question' => $question->question,
                'answer' => $question->answer,
                'status' => $question->status,
                'created_at' => $question->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
