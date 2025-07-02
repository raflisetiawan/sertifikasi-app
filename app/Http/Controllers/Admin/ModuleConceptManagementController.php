<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleConcept;
use App\Services\ModuleConceptManagementService;
use App\Http\Requests\Admin\StoreModuleConceptRequest;
use App\Http\Requests\Admin\UpdateModuleConceptRequest;
use App\Http\Requests\Admin\ReorderModuleConceptRequest;
use Illuminate\Http\Request;

class ModuleConceptManagementController extends Controller
{
    protected $moduleConceptManagementService;

    public function __construct(ModuleConceptManagementService $moduleConceptManagementService)
    {
        $this->moduleConceptManagementService = $moduleConceptManagementService;
    }

    /**
     * Display a listing of module concepts.
     */
    public function index($moduleId)
    {
        $concepts = $this->moduleConceptManagementService->getModuleConcepts($moduleId);
        return response()->json([
            'success' => true,
            'message' => 'Daftar konsep modul berhasil dimuat',
            'data' => $concepts
        ]);
    }

    /**
     * Store a newly created module concept.
     */
    public function store(StoreModuleConceptRequest $request)
    {
        try {
            $concept = $this->moduleConceptManagementService->createModuleConcept($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Konsep modul berhasil ditambahkan',
                'data' => $concept
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan konsep modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified module concept.
     */
    public function show($id)
    {
        try {
            $concept = $this->moduleConceptManagementService->getModuleConceptById($id);
            return response()->json([
                'success' => true,
                'message' => 'Detail konsep modul berhasil dimuat',
                'data' => $concept
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Konsep modul tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail konsep modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified module concept.
     */
    public function update(UpdateModuleConceptRequest $request, ModuleConcept $concept)
    {
        try {
            $concept = $this->moduleConceptManagementService->updateModuleConcept($concept, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Konsep modul berhasil diperbarui',
                'data' => $concept
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui konsep modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified module concept.
     */
    public function destroy(ModuleConcept $concept)
    {
        try {
            $this->moduleConceptManagementService->deleteModuleConcept($concept);
            return response()->json([
                'success' => true,
                'message' => 'Konsep modul berhasil dihapus',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus konsep modul',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder module concepts.
     */
    public function reorder(ReorderModuleConceptRequest $request)
    {
        try {
            $this->moduleConceptManagementService->reorderModuleConcepts($request->validated()['concepts']);
            return response()->json([
                'success' => true,
                'message' => 'Urutan konsep berhasil diperbarui',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui urutan konsep',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}