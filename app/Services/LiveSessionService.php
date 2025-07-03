<?php

namespace App\Services;

use App\Models\LiveSession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LiveSessionService
{
    /**
     * Get all live sessions with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllLiveSessions(int $perPage = 10): LengthAwarePaginator
    {
        return LiveSession::with('course')->paginate($perPage);
    }

    /**
     * Get a single live session by ID.
     *
     * @param int $id
     * @return LiveSession|null
     */
    public function getLiveSessionById(int $id): ?LiveSession
    {
        return LiveSession::find($id);
    }

    /**
     * Create a new live session.
     *
     * @param array $data
     * @return LiveSession
     */
    public function createLiveSession(array $data): LiveSession
    {
        return LiveSession::create($data);
    }

    /**
     * Update an existing live session.
     *
     * @param LiveSession $liveSession
     * @param array $data
     * @return LiveSession
     */
    public function updateLiveSession(LiveSession $liveSession, array $data): LiveSession
    {
        $liveSession->update($data);
        return $liveSession;
    }

    /**
     * Delete a live session.
     *
     * @param LiveSession $liveSession
     * @return bool|null
     */
    public function deleteLiveSession(LiveSession $liveSession): ?bool
    {
        return $liveSession->delete();
    }

    /**
     * Get live sessions for a specific course.
     *
     * @param int $courseId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLiveSessionsByCourse(int $courseId, int $perPage = 10): LengthAwarePaginator
    {
        return LiveSession::where('course_id', $courseId)->with('course')->paginate($perPage);
    }
}
