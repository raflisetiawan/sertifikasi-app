<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Practice;
use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PracticeManagementController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,short_answer,true_false',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array',
            'questions.*.answer_key' => 'required',
            'questions.*.explanation' => 'nullable|string',
            'order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create practice
            $practice = Practice::create([
                'title' => $request->title,
                'description' => $request->description,
                'time_limit_minutes' => $request->time_limit_minutes,
                'questions' => $request->questions
            ]);

            // Create module content
            ModuleContent::create([
                'module_id' => $request->module_id,
                'title' => $request->title,
                'content_type' => 'practice',
                'content_id' => $practice->id,
                'order' => $request->order,
                'is_required' => $request->input('is_required', true)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Practice created successfully',
                'data' => $practice->load('moduleContent')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create practice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Other methods (index, show, update, destroy) remain similar but use Practice model instead
}
