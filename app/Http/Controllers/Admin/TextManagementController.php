<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Text;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TextManagementController extends Controller
{
    /**
     * Display a listing of texts
     */
    public function index()
    {
        $texts = Text::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $texts
        ]);
    }

    /**
     * Store a newly created text content
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'format' => 'required|in:markdown,html',
            'order' => 'required|integer|min:0'
        ], [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'content.required' => 'Konten wajib diisi',
            'format.required' => 'Format wajib diisi',
            'format.in' => 'Format harus markdown atau html',
            'order.required' => 'Urutan wajib diisi',
            'order.integer' => 'Urutan harus berupa angka',
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

            // Create text content
            $text = Text::create([
                'title' => $request->title,
                'content' => $request->content,
                'format' => $request->format
            ]);

            // Create module content entry
            $moduleContent = ModuleContent::create([
                'module_id' => $request->module_id,
                'title' => $request->title,
                'content_type' => 'text',
                'content_id' => $text->id,
                'order' => $request->order,
                'is_required' => $request->input('is_required', true)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Konten teks berhasil ditambahkan',
                'data' => $text->load('moduleContent')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
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
        $text = Text::with('moduleContent')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $text
        ]);
    }

    /**
     * Update the specified text
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'content' => 'string',
            'format' => 'in:markdown,html'
        ], [
            'title.max' => 'Judul maksimal 255 karakter',
            'format.in' => 'Format harus markdown atau html'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $text = Text::findOrFail($id);
            $text->update($validator->validated());

            // Update related module content title if title is changed
            if ($request->has('title')) {
                $text->moduleContent()->update(['title' => $request->title]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Konten teks berhasil diperbarui',
                'data' => $text->load('moduleContent')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $text = Text::findOrFail($id);

            // Delete related module content first
            $text->moduleContent()->delete();

            // Delete the text content
            $text->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Konten teks berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus konten teks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
