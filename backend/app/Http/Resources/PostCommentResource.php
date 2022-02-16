<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class PostCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // $data = parent::toArray($request);

        $entry = $this->entry()->first();
        $data = [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
            'content' => $this->user_id === $request->user()?->id ? $this->content : $this->scrambled_content,
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
