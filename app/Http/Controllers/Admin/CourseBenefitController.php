<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseBenefitRequest;
use App\Http\Requests\Admin\UpdateCourseBenefitRequest;
use App\Models\CourseBenefit;
use App\Http\Resources\CourseBenefitResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CourseBenefitController extends Controller
{
    public function index(): JsonResponse
    {
        $benefits = CourseBenefit::with('course')->latest()->get();
        return response()->json([
            'pesan' => 'Data manfaat kursus berhasil diambil.',
            'data' => CourseBenefitResource::collection($benefits)
        ]);
    }

    public function store(StoreCourseBenefitRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('course-benefits', 'public');
            $data['image'] = $path;
        }

        $benefit = CourseBenefit::create($data);

        return response()->json([
            'pesan' => 'Manfaat kursus berhasil ditambahkan.',
            'data' => new CourseBenefitResource($benefit)
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $benefit = CourseBenefit::with('course')->findOrFail($id);
        return response()->json([
            'pesan' => 'Detail manfaat kursus berhasil ditemukan.',
            'data' => new CourseBenefitResource($benefit)
        ]);
    }

    public function update(UpdateCourseBenefitRequest $request, $id): JsonResponse
    {
        $benefit = CourseBenefit::findOrFail($id);
        Log::debug($request);
        $data = $request->validated();
        Log::debug($data);

        // Cek dan ganti gambar jika di-upload
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($benefit->image && Storage::disk('public')->exists($benefit->image)) {
                Storage::disk('public')->delete($benefit->image);
            }

            // Simpan gambar baru
            $path = $request->file('image')->store('course-benefits', 'public');
            $data['image'] = $path;
        }

        $benefit->update($data);

        return response()->json([
            'pesan' => 'Manfaat kursus berhasil diperbarui.',
            'data' => new CourseBenefitResource($benefit)
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $benefit = CourseBenefit::findOrFail($id);

        // Hapus gambar dari storage jika ada
        if ($benefit->image && Storage::disk('public')->exists($benefit->image)) {
            Storage::disk('public')->delete($benefit->image);
        }

        $benefit->delete();

        return response()->json([
            'pesan' => 'Manfaat kursus berhasil dihapus.'
        ]);
    }


    public function getByCourse($courseId): JsonResponse
    {
        $benefits = CourseBenefit::with('course')
            ->where('course_id', $courseId)
            ->orderByDesc('created_at')
            ->get();

        if ($benefits->isEmpty()) {
            return response()->json([
                'pesan' => 'Tidak ada manfaat yang ditemukan untuk course ID tersebut.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'pesan' => 'Data manfaat berdasarkan course ID berhasil diambil.',
            'data' => CourseBenefitResource::collection($benefits)
        ]);
    }
}
