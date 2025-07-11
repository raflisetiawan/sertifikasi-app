<?php

namespace App\Policies;

use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ThreadPolicy
{
    public function view(User $user, Thread $thread)
    {
        return $user->isEnrolledIn($thread->forum->course);
    }

    public function create(User $user, Forum $forum)
    {
        return $user->isEnrolledIn($forum->course);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Thread $thread): bool
    {
        return $user->id === $thread->user_id && $user->isEnrolledIn($thread->forum->course);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Thread $thread): bool
    {
        return $user->id === $thread->user_id && $user->isEnrolledIn($thread->forum->course);
    }
}