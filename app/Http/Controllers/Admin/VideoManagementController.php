<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\VideoManagementService;
use App\Http\Requests\Admin\StoreVideoRequest;
use App\Http\Requests\Admin\UpdateVideoRequest;
use Illuminate\Http\Request;

class VideoManagementController extends Controller
{
    protected $videoManagementService;

    public function __construct(VideoManagementService $videoManagementService)
    {
        $this->videoManagementService = $videoManagementService;
    }

    /**
     * Display a listing of videos
     */
    public function index()
    {
        $videos = $this->videoManagementService->getAllVideos();
        return response()->json([
            'success' => true,
            'data' => $videos
        ]);
    }

    /**
     * Store a newly created video
     */
    public function store(StoreVideoRequest $request)
    {
        try {
            $video = $this->videoManagementService->createVideo($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Video berhasil ditambahkan',
                'data' => $video
            ], 201);
        } catch (\Exception $e) {
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
        try {
            $video = $this->videoManagementService->getVideoById($id);
            return response()->json([
                'success' => true,
                'data' => $video
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Video tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified video
     */
    public function update(UpdateVideoRequest $request, Video $video)
    {
        try {
            $video = $this->videoManagementService->updateVideo($video, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Video berhasil diperbarui',
                'data' => $video
            ]);
        } catch (\Exception $e) {
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
    public function destroy(Video $video)
    {
        try {
            $this->videoManagementService->deleteVideo($video);
            return response()->json([
                'success' => true,
                'message' => 'Video berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus video',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}