<?php

namespace App\Services;

use App\Models\File;
use App\Models\ModuleContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Exception;

class FileManagementService
{
    public function getAllFiles()
    {
        return File::with('moduleContent')->orderBy('created_at', 'desc')->get();
    }

    public function createFile(array $data, UploadedFile $uploadedFile): File
    {
        return DB::transaction(function () use ($data, $uploadedFile) {
            $filePath = $uploadedFile->store('public/module-files');

            $file = File::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'file_path' => str_replace('public/', '', $filePath),
                'file_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType(),
                'file_size' => $uploadedFile->getSize()
            ]);

            ModuleContent::create([
                'module_id' => $data['module_id'],
                'title' => $data['title'],
                'content_type' => 'file',
                'content_id' => $file->id,
                'order' => $data['order'],
                'is_required' => $data['is_required'] ?? true
            ]);

            return $file->load('moduleContent');
        });
    }

    public function getFileById(int $id): File
    {
        return File::with('moduleContent')->findOrFail($id);
    }

    public function updateFile(File $file, array $data, ?UploadedFile $uploadedFile = null): File
    {
        return DB::transaction(function () use ($file, $data, $uploadedFile) {
            if ($uploadedFile) {
                Storage::delete('public/' . $file->file_path);

                $filePath = $uploadedFile->store('public/module-files');

                $file->update([
                    'file_path' => str_replace('public/', '', $filePath),
                    'file_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getMimeType(),
                    'file_size' => $uploadedFile->getSize()
                ]);
            }

            $file->update($data);

            if (isset($data['title'])) {
                $file->moduleContent()->update(['title' => $data['title']]);
            }

            return $file->load('moduleContent');
        });
    }

    public function deleteFile(File $file): void
    {
        DB::transaction(function () use ($file) {
            Storage::delete('public/' . $file->file_path);
            $file->moduleContent()->delete();
            $file->delete();
        });
    }

    public function downloadFile(File $file): string
    {
        $path = storage_path('app/public/' . $file->file_path);

        if (!file_exists($path)) {
            throw new Exception('File tidak ditemukan');
        }

        return $path;
    }
}
