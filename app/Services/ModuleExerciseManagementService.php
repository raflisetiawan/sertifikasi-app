<?php

namespace App\Services;

use App\Models\ModuleExercise;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Exception;

class ModuleExerciseManagementService
{
    public function getModuleExercises(int $moduleId)
    {
        return ModuleExercise::where('module_id', $moduleId)
            ->orderBy('order')
            ->get();
    }

    public function createModuleExercise(array $data): ModuleExercise
    {
        return ModuleExercise::create($data);
    }

    public function getModuleExerciseById(int $id): ModuleExercise
    {
        return ModuleExercise::findOrFail($id);
    }

    public function updateModuleExercise(ModuleExercise $exercise, array $data): ModuleExercise
    {
        $exercise->update($data);
        return $exercise;
    }

    public function deleteModuleExercise(ModuleExercise $exercise): void
    {
        $exercise->delete();
    }

    public function reorderModuleExercises(array $exercisesData): void
    {
        foreach ($exercisesData as $exerciseData) {
            ModuleExercise::where('id', $exerciseData['id'])
                ->update(['order' => $exerciseData['order']]);
        }
    }
}
