<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LiveSessionRequest;
use App\Http\Resources\LiveSessionResource;
use App\Models\LiveSession;
use App\Services\LiveSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveSessionController extends Controller
{
    protected LiveSessionService $liveSessionService;

    public function __construct(LiveSessionService $liveSessionService)
    {
        $this->liveSessionService = $liveSessionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $liveSessions = $this->liveSessionService->getAllLiveSessions($perPage);
        return LiveSessionResource::collection($liveSessions)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LiveSessionRequest $request): JsonResponse
    {
        $liveSession = $this->liveSessionService->createLiveSession($request->validated());
        return (new LiveSessionResource($liveSession))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LiveSession $liveSession): JsonResponse
    {
        return (new LiveSessionResource($liveSession))->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LiveSessionRequest $request, LiveSession $liveSession): JsonResponse
    {
        $liveSession = $this->liveSessionService->updateLiveSession($liveSession, $request->validated());
        return (new LiveSessionResource($liveSession))->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LiveSession $liveSession): JsonResponse
    {
        $this->liveSessionService->deleteLiveSession($liveSession);
        return response()->json(null, 204);
    }

    /**
     * Display a listing of live sessions for a specific course.
     */
    public function getLiveSessionsByCourse(Request $request, int $courseId): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $liveSessions = $this->liveSessionService->getLiveSessionsByCourse($courseId, $perPage);
        return LiveSessionResource::collection($liveSessions)->response();
    }
}
