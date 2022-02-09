<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        $data['body'] = $this->user()->first()->scrambleText($data['body'], $request->user());
        return $data;
    }
}
