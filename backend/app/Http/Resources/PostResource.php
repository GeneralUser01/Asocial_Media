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
        /*
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        */
        $data = parent::toArray($request);

        // Use the post's author to scramble the post's content (optionally customizing based on viewer):
        // $data['body'] = $this->user()->first()->scrambleText($data['body'], $request->user());

        if ($this->id !== $request->user()?->id) {
            $data['body'] = $this->scrambled_body;
        }

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