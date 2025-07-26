<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreModuleContentRequest;
use App\Http\Requests\Admin\UpdateModuleContentRequest;
use App\Http\Requests\Admin\ReorderModuleContentRequest;
use App\Http\Requests\Admin\ShowModuleContentRequest;
use App\Http\Requests\Admin\UpdateContentOrderRequest;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Services\ModuleContentManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleContentManagementController extends Controller
{
    protected $moduleContentManagementService;

    public function __construct(ModuleContentManagementService $moduleContentManagementService)
    {
        $this->moduleContentManagementService = $moduleContentManagementService;
    }

    /**
     * Display a listing of module contents
     */
    public function index(Module $module)
    {
        $contents = $this->moduleContentManagementService->getModuleContents($module);
        return response()->json([
            'success' => true,
            'data' => $contents
        ]);
    }

    /**
     * Store a newly created module content
     */
    public function store(StoreModuleContentRequest $request, Module $module)
    {
        try {
            $content = $this->moduleContentManagementService->storeModuleContent($module, $request->validated());
            return response()->json([
                'success' => true,
                'data' => $content
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create module content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified module content
     */
    public function show(ShowModuleContentRequest $request, Module $module, ModuleContent $content)
    {
        return response()->json([
            'success' => true,
            'data' => $content->load('content')
        ]);
    }

    /**
     * Update the specified module content
     */
    public function update(UpdateModuleContentRequest $request, Module $module, ModuleContent $content)
    {
        try {
            $content = $this->moduleContentManagementService->updateModuleContent($module, $content, $request->validated());
            return response()->json([
                'success' => true,
                'data' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update module content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified module content and its related content
     */
    public function destroy(Module $module, ModuleContent $content)
    {
        try {
            $this->moduleContentManagementService->deleteModuleContent($module, $content);
            return response()->json([
                'success' => true,
                'message' => 'Module content and related data deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete module content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder multiple contents at once
     */
    public function reorder(ReorderModuleContentRequest $request, Module $module)
    {
        try {
            $this->moduleContentManagementService->reorderModuleContents($module, $request->validated()['contents']);
            return response()->json([
                'success' => true,
                'data' => $module->contents()->orderBy('order')->get()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder contents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the order of multiple contents.
     */
    public function updateOrder(UpdateContentOrderRequest $request, Module $module)
    {
        try {
            $this->moduleContentManagementService->reorderModuleContents($module, $request->validated()['contents']);
            return response()->json(['message' => 'Urutan konten berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui urutan konten.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}