<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    public function create(User $user, Thread $thread)
    {
        return $user->isEnrolledIn($thread->forum->course);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id && $user->isEnrolledIn($post->thread->forum->course);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id && $user->isEnrolledIn($post->thread->forum->course);
    }
}
