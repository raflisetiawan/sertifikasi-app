<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ModuleContentManagementService
{
    public function getModuleContents(Module $module)
    {
        return $module->contents()
            ->with('content')
            ->orderBy('order')
            ->get();
    }

    public function storeModuleContent(Module $module, array $data): ModuleContent
    {
        $this->validateUniqueOrder($module, $data['order']);

        return DB::transaction(function () use ($module, $data) {
            $content = $module->contents()->create($data);
            return $content->load('content');
        });
    }

    public function updateModuleContent(Module $module, ModuleContent $content, array $data): ModuleContent
    {
        if ($content->module_id !== $module->id) {
            throw new Exception('Content does not belong to this module');
        }

        if (isset($data['order']) && $data['order'] !== $content->order) {
            $this->validateUniqueOrder($module, $data['order'], $content->id);
        }

        return DB::transaction(function () use ($content, $data) {
            $content->update($data);
            return $content->load('content');
        });
    }

    public function deleteModuleContent(Module $module, ModuleContent $content): void
    {
        if ($content->module_id !== $module->id) {
            throw new Exception('Content does not belong to this module');
        }

        DB::transaction(function () use ($module, $content) {
            // Delete the related content based on content_type
            switch ($content->content_type) {
                case 'text':
                case 'quiz':
                case 'assignment':
                case 'video':
                    if ($content->content) {
                        $content->content()->delete();
                    }
                    break;
                case 'file':
                    if ($content->content) {
                        if ($content->content->file_path) {
                            Storage::delete('public/' . $content->content->file_path);
                        }
                        $content->content()->delete();
                    }
                    break;
            }

            // Reorder remaining contents
            $module->contents()
                ->where('order', '>', $content->order)
                ->decrement('order');

            // Finally delete the module content entry
            $content->delete();
        });
    }

    public function reorderModuleContents(Module $module, array $contentsData): void
    {
        DB::transaction(function () use ($module, $contentsData) {
            foreach ($contentsData as $item) {
                $content = ModuleContent::find($item['id']);
                if ($content && $content->module_id === $module->id) {
                    $content->update(['order' => $item['order']]);
                }
            }
        });
    }

    private function validateUniqueOrder(Module $module, int $order, ?int $exceptId = null): void
    {
        $query = $module->contents()->where('order', $order);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw new Exception('The order has already been taken for this module.');
        }
    }
}
