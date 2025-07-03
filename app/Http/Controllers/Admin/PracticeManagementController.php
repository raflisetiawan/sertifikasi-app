<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Practice;
use App\Services\PracticeManagementService;
use App\Http\Requests\Admin\StorePracticeRequest;
use App\Http\Requests\Admin\UpdatePracticeRequest;
use Illuminate\Http\Request;

class PracticeManagementController extends Controller
{
    protected $practiceManagementService;

    public function __construct(PracticeManagementService $practiceManagementService)
    {
        $this->practiceManagementService = $practiceManagementService;
    }

    public function index()
    {
        $practices = $this->practiceManagementService->getAllPractices();
        return response()->json([
            'success' => true,
            'data' => $practices
        ]);
    }

    public function store(StorePracticeRequest $request)
    {
        try {
            $practice = $this->practiceManagementService->createPractice($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Practice created successfully',
                'data' => $practice
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create practice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $practice = $this->practiceManagementService->getPracticeById($id);
            return response()->json([
                'success' => true,
                'data' => $practice
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Practice not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve practice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdatePracticeRequest $request, $id)
    {
        try {
            $moduleContent = \App\Models\ModuleContent::findOrFail($id);
            $practice = $moduleContent->content;

            if (!$practice instanceof \App\Models\Practice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konten yang diberikan bukan latihan.'
                ], 400);
            }

            $practice = $this->practiceManagementService->updatePractice($practice, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Practice updated successfully',
                'data' => $practice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update practice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Practice $practice)
    {
        try {
            $this->practiceManagementService->deletePractice($practice);
            return response()->json([
                'success' => true,
                'message' => 'Practice deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete practice',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}