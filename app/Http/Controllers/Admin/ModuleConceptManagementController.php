<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleConcept;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModuleConceptManagementController extends Controller
{
    /**
     * Display a listing of module concepts.
     */
    public function index($moduleId)
    {
        $concepts = ModuleConcept::where('module_id', $moduleId)
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar konsep modul berhasil dimuat',
            'data' => $concepts
        ]);
    }

    /**
     * Store a newly created module concept.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'order' => 'required|integer|min:1'
        ], [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul konsep wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'order.required' => 'Urutan wajib diisi',
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $concept = ModuleConcept::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Konsep modul berhasil ditambahkan',
            'data' => $concept
        ], 201);
    }

    /**
     * Display the specified module concept.
     */
    public function show($id)
    {
        $concept = ModuleConcept::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail konsep modul berhasil dimuat',
            'data' => $concept
        ]);
    }

    /**
     * Update the specified module concept.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'order' => 'sometimes|integer|min:1'
        ], [
            'title.max' => 'Judul maksimal 255 karakter',
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $concept = ModuleConcept::findOrFail($id);

        $updateData = array_filter($request->only([
            'title',
            'order'
        ]), function ($value) {
            return $value !== null;
        });

        $concept->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Konsep modul berhasil diperbarui',
            'data' => $concept
        ]);
    }

    /**
     * Remove the specified module concept.
     */
    public function destroy($id)
    {
        $concept = ModuleConcept::findOrFail($id);
        $concept->delete();

        return response()->json([
            'success' => true,
            'message' => 'Konsep modul berhasil dihapus',
            'data' => null
        ]);
    }

    /**
     * Reorder module concepts.
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'concepts' => 'required|array',
            'concepts.*.id' => 'required|exists:module_concepts,id',
            'concepts.*.order' => 'required|integer|min:1'
        ], [
            'concepts.required' => 'Data konsep wajib diisi',
            'concepts.array' => 'Format data konsep tidak valid',
            'concepts.*.id.required' => 'ID konsep wajib diisi',
            'concepts.*.id.exists' => 'Konsep tidak ditemukan',
            'concepts.*.order.required' => 'Urutan konsep wajib diisi',
            'concepts.*.order.integer' => 'Urutan harus berupa angka',
            'concepts.*.order.min' => 'Urutan minimal 1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        foreach ($request->concepts as $conceptData) {
            ModuleConcept::where('id', $conceptData['id'])
                ->update(['order' => $conceptData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan konsep berhasil diperbarui',
            'data' => null
        ]);
    }
}
