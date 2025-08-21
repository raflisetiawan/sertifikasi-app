<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\ModuleManagementService;
use App\Http\Requests\Admin\StoreModuleRequest;
use App\Http\Requests\Admin\UpdateModuleRequest;
use App\Http\Requests\Admin\ReorderModuleRequest;
use App\Http\Resources\ModuleResource;
use App\Http\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log;

class ModuleManagementController extends Controller
{
    use ApiResponse;

    protected $moduleManagementService;

    public function __construct(ModuleManagementService $moduleManagementService)
    {
        $this->moduleManagementService = $moduleManagementService;
    }
    public function index($courseId)
    {
        $modules = $this->moduleManagementService->getModulesByCourse($courseId);
        return $this->success(ModuleResource::collection($modules), 'Daftar modul berhasil dimuat');
    }

    public function show($id)
    {
        try {
            $module = $this->moduleManagementService->getModuleById($id);
            return $this->success(new ModuleResource($module), 'Detail modul berhasil dimuat');
        } catch (ModelNotFoundException $e) {
            return $this->error('Modul tidak ditemukan', 404, $e->getMessage());
        } catch (Exception $e) {
            return $this->error('Gagal memuat detail modul', 500, $e->getMessage());
        }
    }

    public function store(StoreModuleRequest $request)
    {
        try {
            $module = $this->moduleManagementService->createModule($request->validated(), $request->file('thumbnail'));
            return $this->success(new ModuleResource($module), 'Modul berhasil ditambahkan', 201);
        } catch (Exception $e) {
            return $this->error('Gagal menambahkan modul', 500, $e->getMessage());
        }
    }

    public function update(UpdateModuleRequest $request, Module $module)
    {
        try {
            $module = Module::findOrFail($module->id);
            $module = $this->moduleManagementService->updateModule($module, $request->validated(), $request->file('thumbnail'));
            return $this->success(new ModuleResource($module), 'Module updated successfully');
        } catch (Exception $e) {
            return $this->error('Failed to update module', 500, $e->getMessage());
        }
    }

    public function destroy(Module $module)
    {
        try {
            $this->moduleManagementService->deleteModule($module);
            return $this->success(null, 'Modul berhasil dihapus');
        } catch (Exception $e) {
            return $this->error('Gagal menghapus modul', 500, $e->getMessage());
        }
    }

    public function reorder(ReorderModuleRequest $request)
    {
        try {
            $this->moduleManagementService->reorderModules($request->validated()['modules']);
            return $this->success(null, 'Urutan modul berhasil diperbarui');
        } catch (Exception $e) {
            return $this->error('Gagal memperbarui urutan modul', 500, $e->getMessage());
        }
    }
}
