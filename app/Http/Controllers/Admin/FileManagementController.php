<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Services\FileManagementService;
use App\Http\Requests\Admin\StoreFileRequest;
use App\Http\Requests\Admin\UpdateFileRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagementController extends Controller
{
    protected $fileManagementService;

    public function __construct(FileManagementService $fileManagementService)
    {
        $this->fileManagementService = $fileManagementService;
    }

    /**
     * Display a listing of files
     */
    public function index()
    {
        $files = $this->fileManagementService->getAllFiles();
        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    /**
     * Store a newly created file
     */
    public function store(StoreFileRequest $request)
    {
        try {
            $file = $this->fileManagementService->createFile($request->validated(), $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'File berhasil diunggah',
                'data' => $file
            ], 201);
        } catch (\Exception $e) {
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
        try {
            $file = $this->fileManagementService->getFileById($id);
            return response()->json([
                'success' => true,
                'data' => $file
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified file
     */
    public function update(UpdateFileRequest $request, File $file)
    {
        try {
            $file = $this->fileManagementService->updateFile($file, $request->validated(), $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'File berhasil diperbarui',
                'data' => $file
            ]);
        } catch (\Exception $e) {
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
    public function destroy(File $file)
    {
        try {
            $this->fileManagementService->deleteFile($file);
            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus'
            ]);
        } catch (\Exception $e) {
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
    public function download(File $file)
    {
        try {
            $path = $this->fileManagementService->downloadFile($file);
            return response()->download($path, $file->file_name);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}