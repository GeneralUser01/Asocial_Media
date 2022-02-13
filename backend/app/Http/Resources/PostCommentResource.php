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
        $data = parent::toArray($request);

        // Add likes and dislikes:
        $entry = $this->entry()->first();
        if (Gate::forUser($request->user())->allows('viewLikes', $this->resource)) {
            $data['likes'] = $entry === null ? 0 : $entry->likes()->where('is_like', '=', true)->count();
        }
        if (Gate::forUser($request->user())->allows('viewDislikes', $this->resource)) {
            $data['dislikes'] = $entry === null ? 0 : $entry->likes()->where('is_like', '=', false)->count();
        }

        return $data;
    }
}
