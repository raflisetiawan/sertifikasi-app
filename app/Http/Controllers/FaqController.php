<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;

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
        return response()->json($faqs);
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
        return response()->json($faq);
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
}
