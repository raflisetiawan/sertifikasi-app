<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainerForumController extends Controller
{
    public function index(Course $course)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isTrainer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return response()->json(['message' => 'Trainer profile not found.'], 404);
        }

        // Check if the trainer is assigned to this course
        if (!$trainer->courses->contains($course->id)) {
            return response()->json(['message' => 'You are not assigned to this course.'], 403);
        }

        $forum = $course->forum()->with('threads.user', 'threads.posts.user')->first();

        if (!$forum) {
            return response()->json(['message' => 'Forum not found for this course.'], 404);
        }

        return response()->json($forum);
    }

    public function show(Forum $forum)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isTrainer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return response()->json(['message' => 'Trainer profile not found.'], 404);
        }

        // Check if the trainer is assigned to the course associated with this forum
        if (!$trainer->courses->contains($forum->course_id)) {
            return response()->json(['message' => 'You are not assigned to the course of this forum.'], 403);
        }

        $forum->load('threads.user', 'threads.posts.user');

        return response()->json($forum);
    }

    public function storeThread(Forum $forum, Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isTrainer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return response()->json(['message' => 'Trainer profile not found.'], 404);
        }

        // Check if the trainer is assigned to the course associated with this forum
        if (!$trainer->courses->contains($forum->course_id)) {
            return response()->json(['message' => 'You are not assigned to the course of this forum.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $thread = $forum->threads()->create([
            'user_id' => $user->id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json($thread, 201);
    }

    public function storePost(Thread $thread, Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isTrainer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return response()->json(['message' => 'Trainer profile not found.'], 404);
        }

        // Check if the trainer is assigned to the course associated with this thread's forum
        if (!$trainer->courses->contains($thread->forum->course_id)) {
            return response()->json(['message' => 'You are not assigned to the course of this forum.'], 403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $post = $thread->posts()->create([
            'user_id' => $user->id,
            'body' => $request->content,
        ]);

        return response()->json($post, 201);
    }

    public function showThread(Thread $thread)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isTrainer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $trainer = $user->trainer;

        if (!$trainer) {
            return response()->json(['message' => 'Trainer profile not found.'], 404);
        }

        // Check if the trainer is assigned to the course associated with this thread's forum
        if (!$trainer->courses->contains($thread->forum->course_id)) {
            return response()->json(['message' => 'You are not assigned to the course of this forum.'], 403);
        }

        $thread->load('user', 'posts.user');

        return response()->json($thread);
    }
}
