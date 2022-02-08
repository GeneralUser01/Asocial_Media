<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    // For more info see: https://laravel.com/docs/8.x/eloquent-resources

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($request->user()?->can('viewAllInfo')) {
            // Same info a user would see about themselves:
            return parent::toArray($request);
        }
        // Show only some info to other users:
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($request->user()?->can('viewEmail', $this->resource), $this->email),

            // For more info about filtering a collection see:
            // https://stackoverflow.com/questions/21974402/filtering-eloquent-collection-data-with-collection-filter
            'roles' => $this->roles()->get()->filter(function ($role) use ($request) {
                if ($request->user() === null) return false;
                // Only show roles that we are authorized to view:
                return $request->user()->can('viewRole', [$this->resource, $role]);
            })->values(),

            'created_at' => $this->created_at,
        ];
    }
}
