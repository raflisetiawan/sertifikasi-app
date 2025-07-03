<?php

namespace App\Services;

use App\Models\Video;
use App\Models\ModuleContent;
use Illuminate\Support\Facades\DB;
use Exception;

class VideoManagementService
{
    public function getAllVideos()
    {
        return Video::with('moduleContent')->orderBy('created_at', 'desc')->get();
    }

    public function createVideo(array $data): Video
    {
        return DB::transaction(function () use ($data) {
            $video = Video::create($data);

            ModuleContent::create([
                'module_id' => $data['module_id'],
                'title' => $data['title'],
                'content_type' => 'video',
                'content_id' => $video->id,
                'order' => $data['order'],
                'is_required' => $data['is_required'] ?? true,
                'minimum_duration_seconds' => $data['duration_seconds']
            ]);

            return $video->load('moduleContent');
        });
    }

    public function getVideoById(int $id): Video
    {
        return Video::with('moduleContent')->findOrFail($id);
    }

    public function updateVideo(Video $video, array $data): Video
    {
        return DB::transaction(function () use ($video, $data) {
            $video->update($data);

            $moduleContentData = [];
            if (isset($data['title'])) {
                $moduleContentData['title'] = $data['title'];
            }
            if (isset($data['order'])) {
                $moduleContentData['order'] = $data['order'];
            }
            if (isset($data['is_required'])) {
                $moduleContentData['is_required'] = $data['is_required'];
            }
            if (isset($data['duration_seconds'])) {
                $moduleContentData['minimum_duration_seconds'] = $data['duration_seconds'];
            }

            if (!empty($moduleContentData)) {
                $video->moduleContent()->update($moduleContentData);
            }

            return $video->load('moduleContent');
        });
    }

    public function deleteVideo(Video $video): void
    {
        DB::transaction(function () use ($video) {
            $video->moduleContent()->delete();
            $video->delete();
        });
    }
}
