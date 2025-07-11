<?php

namespace App\Observers;

use App\Models\Post;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        $thread = $post->thread;
        $thread->increment('replies_count');
        $thread->last_activity_at = $post->created_at;
        $thread->save();
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        $thread = $post->thread;
        $thread->decrement('replies_count');

        // Update last activity to the latest post or thread creation time
        $lastPost = $thread->posts()->latest()->first();
        $thread->last_activity_at = $lastPost ? $lastPost->created_at : $thread->created_at;
        $thread->save();
    }
}
