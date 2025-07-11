<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request, Thread $thread)
    {
        $this->authorize('create', [Post::class, $thread]);

        $request->validate([
            'body' => 'required|string',
        ]);

        $post = $thread->posts()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        return response()->json($post, 201);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'body' => 'sometimes|required|string',
        ]);

        $post->update($request->only('body'));

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(null, 204);
    }
}
