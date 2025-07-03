<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Text;
use App\Services\TextManagementService;
use App\Http\Requests\Admin\StoreTextRequest;
use App\Http\Requests\Admin\UpdateTextRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TextManagementController extends Controller
{
    protected $textManagementService;

    public function __construct(TextManagementService $textManagementService)
    {
        $this->textManagementService = $textManagementService;
    }

    /**
     * Display a listing of texts
     */
    public function index()
    {
        $texts = $this->textManagementService->getAllTexts();
        return response()->json([
            'success' => true,
            'data' => $texts
        ]);
    }

    /**
     * Store a newly created text content
     */
    public function store(StoreTextRequest $request)
    {
        try {
            $text = $this->textManagementService->createText($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Konten teks berhasil ditambahkan',
                'data' => $text
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan konten teks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified text
     */
    public function show($id)
    {
        try {
            $text = $this->textManagementService->getTextById($id);
            return response()->json([
                'success' => true,
                'data' => $text
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Konten teks tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat konten teks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified text
     */
    public function update(UpdateTextRequest $request, $id)
    {
        try {
            $moduleContent = \App\Models\ModuleContent::findOrFail($id);
            $text = $moduleContent->content;

            if (!$text instanceof \App\Models\Text) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konten yang diberikan bukan teks.'
                ], 400);
            }

            $text = $this->textManagementService->updateText($text, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Konten teks berhasil diperbarui',
                'data' => $text
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui konten teks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified text
     */
    public function destroy(Text $text)
    {
        try {
            $this->textManagementService->deleteText($text);
            return response()->json([
                'success' => true,
                'message' => 'Konten teks berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus konten teks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
