<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $entry = $this->entry()->first();

        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'body' => $this->id === $request->user()?->id ? $this->body : $this->scrambled_body,
            'has_image' => $this->image_mime_type !== null && $this->image !== null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Add likes and dislikes:
            'likes' => $this->when(Gate::forUser($request->user())->allows('viewLikes', $this->resource), function() use ($entry) {
                return $entry === null ? 0 : $entry->likes()->where('is_like', '=', true)->count();
            }),
            'dislikes' => $this->when(Gate::forUser($request->user())->allows('viewDislikes', $this->resource), function() use ($entry) {
                return $entry === null ? 0 : $entry->likes()->where('is_like', '=', false)->count();
            }),
        ];

        // Use the post's author to scramble the post's content (optionally customizing based on viewer):
        // $data['body'] = $this->user()->first()->scrambleText($data['body'], $request->user());

        if ($request->user() && $entry) {
            $like = $request->user()->likeInfo($entry)->first();
            $opinion = 'neutral';
            if ($like) {
                if ($like->is_like) {
                    $opinion = 'liked';
                } else {
                    $opinion = 'disliked';
                }
            }
            $data['opinion'] = $opinion;
        }

        return $data;
    }
}
