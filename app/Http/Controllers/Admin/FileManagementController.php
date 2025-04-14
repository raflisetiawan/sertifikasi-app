<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileManagementController extends Controller
{
    /**
     * Display a listing of files
     */
    public function index()
    {
        $files = File::with('moduleContent')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    /**
     * Store a newly created file
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240',
            'order' => 'required|integer|min:0'
        ], [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul file wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'file.required' => 'File wajib diunggah',
            'file.file' => 'Upload harus berupa file',
            'file.max' => 'Ukuran file maksimal 10MB',
            'order.required' => 'Urutan wajib diisi',
            'order.min' => 'Urutan minimal 0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $uploadedFile = $request->file('file');
            $filePath = $uploadedFile->store('public/module-files');

            // Create file record
            $file = File::create([
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => str_replace('public/', '', $filePath),
                'file_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType(),
                'file_size' => $uploadedFile->getSize()
            ]);

            // Create module content entry
            $moduleContent = ModuleContent::create([
                'module_id' => $request->module_id,
                'title' => $request->title,
                'content_type' => 'file',
                'content_id' => $file->id,
                'order' => $request->order,
                'is_required' => $request->input('is_required', true)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diunggah',
                'data' => $file->load('moduleContent')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($filePath)) {
                Storage::delete($filePath);
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified file
     */
    public function show($id)
    {
        $file = File::with('moduleContent')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $file
        ]);
    }

    /**
     * Update the specified file
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240' // Max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $file = File::findOrFail($id);

            // Handle file replacement if new file is uploaded
            if ($request->hasFile('file')) {
                // Delete old file
                Storage::delete('public/' . $file->file_path);

                // Store new file
                $uploadedFile = $request->file('file');
                $filePath = $uploadedFile->store('public/module-files');

                $file->update([
                    'file_path' => str_replace('public/', '', $filePath),
                    'file_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getMimeType(),
                    'file_size' => $uploadedFile->getSize()
                ]);
            }

            // Update other fields
            $file->update($validator->validated());

            // Update related module content title if title is changed
            if ($request->has('title')) {
                $file->moduleContent()->update(['title' => $request->title]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diperbarui',
                'data' => $file->load('moduleContent')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified file
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $file = File::findOrFail($id);

            // Delete physical file
            Storage::delete('public/' . $file->file_path);

            // Delete related module content first
            $file->moduleContent()->delete();

            // Delete the file record
            $file->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the file
     */
    public function download($id)
    {
        $file = File::findOrFail($id);
        $path = storage_path('app/public/' . $file->file_path);

        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ], 404);
        }

        return response()->download($path, $file->file_name);
    }
}
