<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ForumResource;
use App\Models\Course;
use App\Models\Forum;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index(Course $course)
    {
        $forum = $course->forum()->with('threads.user', 'threads.posts.user')->first();

        if (!$forum) {
            return response()->json(['message' => 'Forum not found.'], 404);
        }

        return new ForumResource($forum);
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $forum = $course->forum()->create($request->all());

        return response()->json($forum, 201);
    }

    public function show(Course $course)
    {
        $forum = $course->forum()->with('threads.user', 'threads.posts.user')->first();

        if (!$forum) {
            return response()->json(['message' => 'Forum not found.'], 404);
        }

        return new ForumResource($forum);
    }

    public function update(Request $request, Course $course, Forum $forum)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $forum->update($request->all());

        return response()->json($forum);
    }

    public function destroy(Course $course, Forum $forum)
    {
        $forum->delete();

        return response()->json(null, 204);
    }
}
