<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseBenefitRequest;
use App\Http\Requests\Admin\UpdateCourseBenefitRequest;
use App\Models\CourseBenefit;
use App\Http\Resources\CourseBenefitResource;
use App\Services\CourseBenefitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CourseBenefitController extends Controller
{
    protected $courseBenefitService;

    public function __construct(CourseBenefitService $courseBenefitService)
    {
        $this->courseBenefitService = $courseBenefitService;
    }

    public function index(): JsonResponse
    {
        $benefits = $this->courseBenefitService->getAllCourseBenefits();
        return response()->json([
            'pesan' => 'Data manfaat kursus berhasil diambil.',
            'data' => CourseBenefitResource::collection($benefits)
        ]);
    }

    public function store(StoreCourseBenefitRequest $request): JsonResponse
    {
        try {
            $benefit = $this->courseBenefitService->createCourseBenefit($request->validated());
            return response()->json([
                'pesan' => 'Manfaat kursus berhasil ditambahkan.',
                'data' => $benefit
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to store course benefit', ['error' => $e->getMessage()]);
            return response()->json([
                'pesan' => 'Gagal menambahkan manfaat kursus.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $benefit = $this->courseBenefitService->getCourseBenefitById($id);
            return response()->json([
                'pesan' => 'Detail manfaat kursus berhasil ditemukan.',
                'data' => $benefit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'pesan' => 'Manfaat kursus tidak ditemukan.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(UpdateCourseBenefitRequest $request, CourseBenefit $benefit): JsonResponse
    {
        try {
            $benefit = $this->courseBenefitService->updateCourseBenefit($benefit, $request->validated());
            return response()->json([
                'pesan' => 'Manfaat kursus berhasil diperbarui.',
                'data' => $benefit
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update course benefit', ['error' => $e->getMessage()]);
            return response()->json([
                'pesan' => 'Gagal memperbarui manfaat kursus.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(CourseBenefit $benefit): JsonResponse
    {
        try {
            $this->courseBenefitService->deleteCourseBenefit($benefit);
            return response()->json([
                'pesan' => 'Manfaat kursus berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete course benefit', ['error' => $e->getMessage()]);
            return response()->json([
                'pesan' => 'Gagal menghapus manfaat kursus.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByCourse($courseId): JsonResponse
    {
        try {
            $benefits = $this->courseBenefitService->getCourseBenefitsByCourseId($courseId);
            return response()->json([
                'pesan' => 'Data manfaat berdasarkan course ID berhasil diambil.',
                'data' => $benefits
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'pesan' => $e->getMessage(),
                'data' => []
            ], 404);
        }
    }
}