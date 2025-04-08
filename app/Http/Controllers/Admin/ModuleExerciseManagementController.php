<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleExercise;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModuleExerciseManagementController extends Controller
{
    /**
     * Display a listing of module exercises.
     */
    public function index($moduleId)
    {
        $exercises = ModuleExercise::where('module_id', $moduleId)
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar latihan modul berhasil dimuat',
            'data' => $exercises
        ]);
    }

    /**
     * Store a newly created module exercise.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'description' => 'required|string',
            'order' => 'required|integer|min:1'
        ], [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'description.required' => 'Deskripsi latihan wajib diisi',
            'order.required' => 'Urutan wajib diisi',
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $exercise = ModuleExercise::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Latihan modul berhasil ditambahkan',
            'data' => $exercise
        ], 201);
    }

    /**
     * Display the specified module exercise.
     */
    public function show($id)
    {
        $exercise = ModuleExercise::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail latihan modul berhasil dimuat',
            'data' => $exercise
        ]);
    }

    /**
     * Update the specified module exercise.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|string',
            'order' => 'sometimes|integer|min:1'
        ], [
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $exercise = ModuleExercise::findOrFail($id);

        $updateData = array_filter($request->only([
            'description',
            'order'
        ]), function ($value) {
            return $value !== null;
        });

        $exercise->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Latihan modul berhasil diperbarui',
            'data' => $exercise
        ]);
    }

    /**
     * Remove the specified module exercise.
     */
    public function destroy($id)
    {
        $exercise = ModuleExercise::findOrFail($id);
        $exercise->delete();

        return response()->json([
            'success' => true,
            'message' => 'Latihan modul berhasil dihapus',
            'data' => null
        ]);
    }

    /**
     * Reorder module exercises.
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exercises' => 'required|array',
            'exercises.*.id' => 'required|exists:module_exercises,id',
            'exercises.*.order' => 'required|integer|min:1'
        ], [
            'exercises.required' => 'Data latihan wajib diisi',
            'exercises.array' => 'Format data latihan tidak valid',
            'exercises.*.id.required' => 'ID latihan wajib diisi',
            'exercises.*.id.exists' => 'Latihan tidak ditemukan',
            'exercises.*.order.required' => 'Urutan latihan wajib diisi',
            'exercises.*.order.integer' => 'Urutan harus berupa angka',
            'exercises.*.order.min' => 'Urutan minimal 1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        foreach ($request->exercises as $exerciseData) {
            ModuleExercise::where('id', $exerciseData['id'])
                ->update(['order' => $exerciseData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan latihan berhasil diperbarui',
            'data' => null
        ]);
    }
}
