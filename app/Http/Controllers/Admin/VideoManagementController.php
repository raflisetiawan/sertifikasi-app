<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VideoManagementController extends Controller
{
    /**
     * Display a listing of videos
     */
    public function index()
    {
        $videos = Video::with('moduleContent')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $videos
        ]);
    }

    /**
     * Store a newly created video
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'required|url',
            'provider' => 'required|in:youtube,vimeo',
            'video_id' => 'required|string',
            'duration_seconds' => 'required|integer|min:1',
            'thumbnail_url' => 'nullable|url',
            'is_downloadable' => 'boolean',
            'captions' => 'nullable|array',
            'order' => 'required|integer|min:0'
        ], [
            'module_id.required' => 'ID modul wajib diisi',
            'module_id.exists' => 'Modul tidak ditemukan',
            'title.required' => 'Judul video wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            'video_url.required' => 'URL video wajib diisi',
            'video_url.url' => 'Format URL video tidak valid',
            'provider.required' => 'Provider video wajib diisi',
            'provider.in' => 'Provider video tidak valid',
            'video_id.required' => 'ID video wajib diisi',
            'duration_seconds.required' => 'Durasi video wajib diisi',
            'duration_seconds.min' => 'Durasi minimal 1 detik',
            'order.required' => 'Urutan wajib diisi',
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

            // Create video
            $video = Video::create($validator->validated());

            // Create module content entry
            $moduleContent = ModuleContent::create([
                'module_id' => $request->module_id,
                'title' => $request->title,
                'content_type' => 'video',
                'content_id' => $video->id,
                'order' => $request->order,
                'is_required' => $request->input('is_required', true),
                'minimum_duration_seconds' => $request->duration_seconds
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Video berhasil ditambahkan',
                'data' => $video->load('moduleContent')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified video
     */
    public function show($id)
    {
        $video = Video::with('moduleContent')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $video
        ]);
    }

    /**
     * Update the specified video
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'sometimes|url',
            'provider' => 'sometimes|in:youtube,vimeo',
            'video_id' => 'sometimes|string',
            'duration_seconds' => 'sometimes|integer|min:1',
            'thumbnail_url' => 'nullable|url',
            'is_downloadable' => 'boolean',
            'captions' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $video = Video::findOrFail($id);
            $video->update($validator->validated());

            // Update related module content title if title is changed
            if ($request->has('title')) {
                $video->moduleContent()->update(['title' => $request->title]);
            }

            // Update minimum duration if duration changed
            if ($request->has('duration_seconds')) {
                $video->moduleContent()->update([
                    'minimum_duration_seconds' => $request->duration_seconds
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Video berhasil diperbarui',
                'data' => $video->load('moduleContent')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified video
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $video = Video::findOrFail($id);

            // Delete related module content first
            $video->moduleContent()->delete();

            // Delete the video
            $video->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Video berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus video',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
