<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleExercise;
use App\Services\ModuleExerciseManagementService;
use App\Http\Requests\Admin\StoreModuleExerciseRequest;
use App\Http\Requests\Admin\UpdateModuleExerciseRequest;
use App\Http\Requests\Admin\ReorderModuleExerciseRequest;
use Illuminate\Http\Request;

class ModuleExerciseManagementController extends Controller
{
    protected $moduleExerciseManagementService;

    public function __construct(ModuleExerciseManagementService $moduleExerciseManagementService)
    {
        $this->moduleExerciseManagementService = $moduleExerciseManagementService;
    }

    /**
     * Display a listing of module exercises.
     */
    public function index($moduleId)
    {
        $exercises = $this->moduleExerciseManagementService->getModuleExercises($moduleId);
        return response()->json([
            'success' => true,
            'message' => 'Daftar latihan modul berhasil dimuat',
            'data' => $exercises
        ]);
    }

    /**
     * Store a newly created module exercise.
     */
    public function store(StoreModuleExerciseRequest $request)
    {
        try {
            $exercise = $this->moduleExerciseManagementService->createModuleExercise($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Latihan modul berhasil ditambahkan',
                'data' => $exercise
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan latihan modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified module exercise.
     */
    public function show($id)
    {
        try {
            $exercise = $this->moduleExerciseManagementService->getModuleExerciseById($id);
            return response()->json([
                'success' => true,
                'message' => 'Detail latihan modul berhasil dimuat',
                'data' => $exercise
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Latihan modul tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail latihan modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified module exercise.
     */
    public function update(UpdateModuleExerciseRequest $request, ModuleExercise $exercise)
    {
        try {
            $exercise = $this->moduleExerciseManagementService->updateModuleExercise($exercise, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Latihan modul berhasil diperbarui',
                'data' => $exercise
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui latihan modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified module exercise.
     */
    public function destroy(ModuleExercise $exercise)
    {
        try {
            $this->moduleExerciseManagementService->deleteModuleExercise($exercise);
            return response()->json([
                'success' => true,
                'message' => 'Latihan modul berhasil dihapus',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus latihan modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder module exercises.
     */
    public function reorder(ReorderModuleExerciseRequest $request)
    {
        try {
            $this->moduleExerciseManagementService->reorderModuleExercises($request->validated()['exercises']);
            return response()->json([
                'success' => true,
                'message' => 'Urutan latihan berhasil diperbarui',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui urutan latihan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}