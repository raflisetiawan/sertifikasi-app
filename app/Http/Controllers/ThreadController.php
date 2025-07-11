<?php

namespace App\Http\Controllers;

use App\Http\Resources\ThreadResource;
use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    public function store(Request $request, Forum $forum)
    {
        $this->authorize('create', [Thread::class, $forum]);

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $thread = $forum->threads()->create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'body' => $request->body,
        ]);

        return response()->json($thread, 201);
    }

    public function show(Thread $thread)
    {
        $this->authorize('view', $thread);
        return new ThreadResource($thread->load('user', 'posts.user'));
    }

    public function update(Request $request, Thread $thread)
    {
        $this->authorize('update', $thread);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
        ]);

        $thread->update($request->only('title', 'body'));

        return response()->json($thread);
    }

    public function destroy(Thread $thread)
    {
        $this->authorize('delete', $thread);

        $thread->delete();

        return response()->json(null, 204);
    }
}
