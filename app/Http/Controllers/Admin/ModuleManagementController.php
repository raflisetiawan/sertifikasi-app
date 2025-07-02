<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\ModuleManagementService;
use App\Http\Requests\Admin\StoreModuleRequest;
use App\Http\Requests\Admin\UpdateModuleRequest;
use App\Http\Requests\Admin\ReorderModuleRequest;
use Illuminate\Http\Request;

class ModuleManagementController extends Controller
{
    protected $moduleManagementService;

    public function __construct(ModuleManagementService $moduleManagementService)
    {
        $this->moduleManagementService = $moduleManagementService;
    }

    /**
     * Display a listing of modules for a specific course.
     */
    public function index($courseId)
    {
        $modules = $this->moduleManagementService->getModulesByCourse($courseId);
        return response()->json([
            'success' => true,
            'message' => 'Daftar modul berhasil dimuat',
            'data' => $modules
        ]);
    }

    public function show($id)
    {
        try {
            $module = $this->moduleManagementService->getModuleById($id);
            return response()->json([
                'success' => true,
                'message' => 'Detail modul berhasil dimuat',
                'data' => $module
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Modul tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created module.
     */
    public function store(StoreModuleRequest $request)
    {
        try {
            $module = $this->moduleManagementService->createModule($request->validated(), $request->file('thumbnail'));
            return response()->json([
                'success' => true,
                'message' => 'Modul berhasil ditambahkan',
                'data' => $module
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified module.
     */
    public function update(UpdateModuleRequest $request, Module $module)
    {
        try {
            $module = $this->moduleManagementService->updateModule($module, $request->validated(), $request->file('thumbnail'));
            return response()->json([
                'success' => true,
                'message' => 'Module updated successfully',
                'data' => $module
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update module',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified module.
     */
    public function destroy(Module $module)
    {
        try {
            $this->moduleManagementService->deleteModule($module);
            return response()->json([
                'success' => true,
                'message' => 'Modul berhasil dihapus',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder modules within a course.
     */
    public function reorder(ReorderModuleRequest $request)
    {
        try {
            $this->moduleManagementService->reorderModules($request->validated()['modules']);
            return response()->json([
                'success' => true,
                'message' => 'Urutan modul berhasil diperbarui',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui urutan modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}