<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'replies_count' => $this->replies_count,
            'last_activity_at' => $this->last_activity_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'posts' => PostResource::collection($this->whenLoaded('posts')),
        ];
    }
}
