<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Exception;

class ModuleManagementService
{
    public function getModulesByCourse(int $courseId)
    {
        return Module::where('course_id', $courseId)
            ->orderBy('order')
            ->get();
    }

    public function getModuleById(int $id): Module
    {
        return Module::findOrFail($id);
    }

    public function createModule(array $data, ?UploadedFile $thumbnail = null): Module
    {
        return DB::transaction(function () use ($data, $thumbnail) {
            $thumbnailPath = null;
            if ($thumbnail) {
                $thumbnailPath = $thumbnail->hashName();
                $thumbnail->storeAs('public/modules/thumbnails', $thumbnailPath);
            }

            $module = Module::create([
                'course_id' => $data['course_id'],
                'order' => $data['order'],
                'type' => $data['type'],
                'estimated_time_min' => $data['estimated_time_min'],
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'description' => $data['description'],
                'thumbnail' => $thumbnailPath,
                'is_access_restricted' => $data['is_access_restricted'] ?? false,
                'access_start_at' => $data['access_start_at'] ?? null,
                'access_end_at' => $data['access_end_at'] ?? null
            ]);

            return $module;
        });
    }

    public function updateModule(Module $module, array $data, ?UploadedFile $thumbnail = null): Module
    {
        return DB::transaction(function () use ($module, $data, $thumbnail) {
            if ($thumbnail) {
                if ($module->thumbnail) {
                    Storage::delete('public/modules/thumbnails/' . $module->thumbnail);
                }
                $thumbnailPath = $thumbnail->hashName();
                $thumbnail->storeAs('public/modules/thumbnails', $thumbnailPath);
                $data['thumbnail'] = $thumbnailPath;
            } else if (array_key_exists('thumbnail', $data) && is_null($data['thumbnail'])) {
                // If thumbnail is explicitly set to null, delete existing one
                if ($module->thumbnail) {
                    Storage::delete('public/modules/thumbnails/' . $module->thumbnail);
                }
                $data['thumbnail'] = null;
            }

            $module->update($data);

            return $module;
        });
    }

    public function deleteModule(Module $module): void
    {
        DB::transaction(function () use ($module) {
            if ($module->thumbnail) {
                Storage::delete('public/modules/thumbnails/' . $module->thumbnail);
            }
            $module->delete();
        });
    }

    public function reorderModules(array $modulesData): void
    {
        foreach ($modulesData as $moduleData) {
            Module::where('id', $moduleData['id'])
                ->update(['order' => $moduleData['order']]);
        }
    }
}
