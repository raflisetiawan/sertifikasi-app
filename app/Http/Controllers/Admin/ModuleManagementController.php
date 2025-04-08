<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ModuleManagementController extends Controller
{
    /**
     * Display a listing of modules for a specific course.
     */
    public function index($courseId)
    {
        $modules = Module::where('course_id', $courseId)
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar modul berhasil dimuat',
            'data' => $modules
        ]);
    }

    /**
     * Store a newly created module.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'order' => 'required|integer|min:1',
            'type' => 'required|in:prework,module,final',
            'estimated_time_min' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'course_id.required' => 'ID kursus wajib diisi',
            'course_id.exists' => 'Kursus tidak ditemukan',
            'order.required' => 'Urutan modul wajib diisi',
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1',
            'type.required' => 'Tipe modul wajib diisi',
            'type.in' => 'Tipe modul tidak valid',
            'estimated_time_min.required' => 'Estimasi waktu wajib diisi',
            'estimated_time_min.integer' => 'Estimasi waktu harus berupa angka',
            'estimated_time_min.min' => 'Estimasi waktu minimal 1 menit',
            'title.required' => 'Judul modul wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'description.required' => 'Deskripsi wajib diisi',
            'thumbnail.image' => 'File harus berupa gambar',
            'thumbnail.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'thumbnail.max' => 'Ukuran gambar maksimal 2MB'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Handle thumbnail upload
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailPath = $thumbnail->hashName();
            $thumbnail->storeAs('public/modules/thumbnails', $thumbnailPath);
        }

        $module = Module::create([
            'course_id' => $request->course_id,
            'order' => $request->order,
            'type' => $request->type,
            'estimated_time_min' => $request->estimated_time_min,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'description' => $request->description,
            'thumbnail' => $thumbnailPath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modul berhasil ditambahkan',
            'data' => $module
        ], 201);
    }

    /**
     * Display the specified module.
     */
    public function show($id)
    {
        $module = Module::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail modul berhasil dimuat',
            'data' => $module
        ]);
    }

    /**
     * Update the specified module.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'order' => 'sometimes|integer|min:1',
            'type' => 'sometimes|in:prework,module,final',
            'estimated_time_min' => 'sometimes|integer|min:1',
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1',
            'type.in' => 'Tipe modul tidak valid',
            'estimated_time_min.integer' => 'Estimasi waktu harus berupa angka',
            'estimated_time_min.min' => 'Estimasi waktu minimal 1 menit',
            'title.max' => 'Judul maksimal 255 karakter',
            'thumbnail.image' => 'File harus berupa gambar',
            'thumbnail.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'thumbnail.max' => 'Ukuran gambar maksimal 2MB'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $module = Module::findOrFail($id);

        // Handle thumbnail update if provided
        if ($request->hasFile('thumbnail')) {
            if ($module->thumbnail) {
                Storage::delete('public/modules/thumbnails/' . $module->thumbnail);
            }
            $thumbnail = $request->file('thumbnail');
            $thumbnailPath = $thumbnail->hashName();
            $thumbnail->storeAs('public/modules/thumbnails', $thumbnailPath);
            $module->thumbnail = $thumbnailPath;
        }

        // Only update fields that were provided in the request
        $updateData = array_filter($request->only([
            'order',
            'type',
            'estimated_time_min',
            'title',
            'subtitle',
            'description'
        ]), function ($value) {
            return $value !== null;
        });

        $module->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Modul berhasil diperbarui',
            'data' => $module
        ]);
    }

    /**
     * Remove the specified module.
     */
    public function destroy($id)
    {
        $module = Module::findOrFail($id);

        // Delete thumbnail if exists
        if ($module->thumbnail) {
            Storage::delete('public/modules/thumbnails/' . $module->thumbnail);
        }

        $module->delete();

        return response()->json([
            'success' => true,
            'message' => 'Modul berhasil dihapus',
            'data' => null
        ]);
    }

    /**
     * Reorder modules within a course.
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer|min:1'
        ], [
            'modules.required' => 'Data modul wajib diisi',
            'modules.array' => 'Format data modul tidak valid',
            'modules.*.id.required' => 'ID modul wajib diisi',
            'modules.*.id.exists' => 'Modul tidak ditemukan',
            'modules.*.order.required' => 'Urutan modul wajib diisi',
            'modules.*.order.integer' => 'Urutan harus berupa angka',
            'modules.*.order.min' => 'Urutan minimal 1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        foreach ($request->modules as $moduleData) {
            Module::where('id', $moduleData['id'])
                ->update(['order' => $moduleData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan modul berhasil diperbarui',
            'data' => null
        ]);
    }
}
