<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\ModuleContent;
use Illuminate\Support\Facades\DB;
use Exception;

class AssignmentManagementService
{
    public function getAllAssignments()
    {
        return Assignment::with('moduleContent')->orderBy('created_at', 'desc')->get();
    }

    public function createAssignment(array $data): Assignment
    {
        return DB::transaction(function () use ($data) {
            $assignment = Assignment::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'instructions' => $data['instructions'],
                'submission_requirements' => $data['submission_requirements'],
                'due_date' => $data['due_date'],
                'max_file_size_mb' => $data['max_file_size_mb'],
                'allowed_file_types' => $data['allowed_file_types']
            ]);

            ModuleContent::create([
                'module_id' => $data['module_id'],
                'title' => $data['title'],
                'content_type' => 'assignment',
                'content_id' => $assignment->id,
                'order' => $data['order'],
                'is_required' => $data['is_required'] ?? true
            ]);

            return $assignment->load('moduleContent');
        });
    }

    public function getAssignmentById(int $id): Assignment
    {
        return Assignment::with('moduleContent')->findOrFail($id);
    }

    public function updateAssignment(Assignment $assignment, array $data): Assignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $assignment->update($data);

            if (isset($data['title'])) {
                $assignment->moduleContent()->update(['title' => $data['title']]);
            }

            return $assignment->load('moduleContent');
        });
    }

    public function deleteAssignment(Assignment $assignment): void
    {
        DB::transaction(function () use ($assignment) {
            $assignment->moduleContent()->delete();
            $assignment->delete();
        });
    }
}
