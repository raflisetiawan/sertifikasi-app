<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AssignmentManagementController extends Controller
{
    /**
     * Display a listing of assignments
     */
    public function index()
    {
        $assignments = Assignment::with('moduleContent')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $assignments
        ]);
    }

    /**
     * Store a newly created assignment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructions' => 'required|string',
            'submission_requirements' => 'required|array',
            'due_date' => 'required|date|after:now',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
            'allowed_file_types' => 'required|string',
            'order' => 'required|integer|min:0'
        ], [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul tugas wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'description.required' => 'Deskripsi wajib diisi',
            'instructions.required' => 'Instruksi wajib diisi',
            'submission_requirements.required' => 'Persyaratan submission wajib diisi',
            'submission_requirements.array' => 'Format persyaratan submission tidak valid',
            'due_date.required' => 'Tanggal deadline wajib diisi',
            'due_date.date' => 'Format tanggal tidak valid',
            'due_date.after' => 'Tanggal deadline harus setelah waktu sekarang',
            'max_file_size_mb.required' => 'Ukuran file maksimal wajib diisi',
            'max_file_size_mb.integer' => 'Ukuran file harus berupa angka',
            'max_file_size_mb.min' => 'Ukuran file minimal 1MB',
            'max_file_size_mb.max' => 'Ukuran file maksimal 100MB',
            'allowed_file_types.required' => 'Tipe file yang diizinkan wajib diisi',
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

            // Create assignment
            $assignment = Assignment::create([
                'title' => $request->title,
                'description' => $request->description,
                'instructions' => $request->instructions,
                'submission_requirements' => $request->submission_requirements,
                'due_date' => $request->due_date,
                'max_file_size_mb' => $request->max_file_size_mb,
                'allowed_file_types' => $request->allowed_file_types
            ]);

            // Create module content entry
            $moduleContent = ModuleContent::create([
                'module_id' => $request->module_id,
                'title' => $request->title,
                'content_type' => 'assignment',
                'content_id' => $assignment->id,
                'order' => $request->order,
                'is_required' => $request->input('is_required', true)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil ditambahkan',
                'data' => $assignment->load('moduleContent')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tugas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified assignment
     */
    public function show($id)
    {
        $assignment = Assignment::with('moduleContent')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $assignment
        ]);
    }

    /**
     * Update the specified assignment
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'instructions' => 'sometimes|string',
            'submission_requirements' => 'sometimes|array',
            'due_date' => 'sometimes|date|after:now',
            'max_file_size_mb' => 'sometimes|integer|min:1|max:100',
            'allowed_file_types' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $assignment = Assignment::findOrFail($id);
            $assignment->update($validator->validated());

            // Update related module content title if title is changed
            if ($request->has('title')) {
                $assignment->moduleContent()->update(['title' => $request->title]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diperbarui',
                'data' => $assignment->load('moduleContent')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui tugas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified assignment
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $assignment = Assignment::findOrFail($id);

            // Delete related module content first
            $assignment->moduleContent()->delete();

            // Delete the assignment
            $assignment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tugas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
