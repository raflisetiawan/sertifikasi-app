<?php

namespace App\Services;

use App\Models\Practice;
use App\Models\ModuleContent;
use Illuminate\Support\Facades\DB;
use Exception;

class PracticeManagementService
{
    public function getAllPractices()
    {
        return Practice::with('moduleContent')->orderBy('created_at', 'desc')->get();
    }

    public function createPractice(array $data): Practice
    {
        return DB::transaction(function () use ($data) {
            $practice = Practice::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'questions' => $data['questions']
            ]);

            ModuleContent::create([
                'module_id' => $data['module_id'],
                'title' => $data['title'],
                'content_type' => 'practice',
                'content_id' => $practice->id,
                'order' => $data['order'],
                'is_required' => $data['is_required'] ?? true
            ]);

            return $practice->load('moduleContent');
        });
    }

    public function getPracticeById(int $id): Practice
    {
        return Practice::with('moduleContent')->findOrFail($id);
    }

    public function updatePractice(Practice $practice, array $data): Practice
    {
        return DB::transaction(function () use ($practice, $data) {
            $practice->update($data);

            $moduleContentData = [];
            if (isset($data['title'])) {
                $moduleContentData['title'] = $data['title'];
            }
            if (isset($data['order'])) {
                $moduleContentData['order'] = $data['order'];
            }
            if (isset($data['is_required'])) {
                $moduleContentData['is_required'] = $data['is_required'];
            }

            if (!empty($moduleContentData)) {
                $practice->moduleContent()->update($moduleContentData);
            }

            return $practice->load('moduleContent');
        });
    }

    public function deletePractice(Practice $practice): void
    {
        DB::transaction(function () use ($practice) {
            $practice->moduleContent()->delete();
            $practice->delete();
        });
    }
}
