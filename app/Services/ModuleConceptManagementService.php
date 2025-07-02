<?php

namespace App\Services;

use App\Models\ModuleConcept;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Exception;

class ModuleConceptManagementService
{
    public function getModuleConcepts(int $moduleId)
    {
        return ModuleConcept::where('module_id', $moduleId)
            ->orderBy('order')
            ->get();
    }

    public function createModuleConcept(array $data): ModuleConcept
    {
        return ModuleConcept::create($data);
    }

    public function getModuleConceptById(int $id): ModuleConcept
    {
        return ModuleConcept::findOrFail($id);
    }

    public function updateModuleConcept(ModuleConcept $concept, array $data): ModuleConcept
    {
        $concept->update($data);
        return $concept;
    }

    public function deleteModuleConcept(ModuleConcept $concept): void
    {
        $concept->delete();
    }

    public function reorderModuleConcepts(array $conceptsData): void
    {
        foreach ($conceptsData as $conceptData) {
            ModuleConcept::where('id', $conceptData['id'])
                ->update(['order' => $conceptData['order']]);
        }
    }
}
