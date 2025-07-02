<?php

namespace App\Services;

use App\Models\CourseBenefit;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CourseBenefitResource;
use Exception;

class CourseBenefitService
{
    public function getAllCourseBenefits()
    {
        return CourseBenefit::with('course')->latest()->get();
    }

    public function createCourseBenefit(array $data)
    {
        if (isset($data['image'])) {
            $path = $data['image']->store('course-benefits', 'public');
            $data['image'] = $path;
        }

        $benefit = CourseBenefit::create($data);
        return new CourseBenefitResource($benefit);
    }

    public function getCourseBenefitById(int $id)
    {
        $benefit = CourseBenefit::with('course')->findOrFail($id);
        return new CourseBenefitResource($benefit);
    }

    public function updateCourseBenefit(CourseBenefit $benefit, array $data)
    {
        if (isset($data['image'])) {
            if ($benefit->image && Storage::disk('public')->exists($benefit->image)) {
                Storage::disk('public')->delete($benefit->image);
            }
            $path = $data['image']->store('course-benefits', 'public');
            $data['image'] = $path;
        }

        $benefit->update($data);
        return new CourseBenefitResource($benefit);
    }

    public function deleteCourseBenefit(CourseBenefit $benefit)
    {
        if ($benefit->image && Storage::disk('public')->exists($benefit->image)) {
            Storage::disk('public')->delete($benefit->image);
        }
        $benefit->delete();
    }

    public function getCourseBenefitsByCourseId(int $courseId)
    {
        $benefits = CourseBenefit::with('course')
            ->where('course_id', $courseId)
            ->orderByDesc('created_at')
            ->get();

        if ($benefits->isEmpty()) {
            throw new Exception('Tidak ada manfaat yang ditemukan untuk course ID tersebut.');
        }

        return CourseBenefitResource::collection($benefits);
    }
}
