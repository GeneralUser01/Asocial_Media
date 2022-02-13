<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

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
        $data = null;
        if (Gate::forUser($request->user())->allows('viewAllInfo', $this->resource)) {
            // Same info a user would see about themselves:

            // Make all roles with their names visible in the response:
            $request->user()->load('roles');

            $data = parent::toArray($request);
        } else {
            // Show only some info to other users:
            $data = [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->when(Gate::forUser($request->user())->allows('viewEmail', $this->resource), $this->email),

                // For more info about filtering a collection see:
                // https://stackoverflow.com/questions/21974402/filtering-eloquent-collection-data-with-collection-filter
                'roles' => $this->roles()->get()->filter(function ($role) use ($request) {
                    return Gate::forUser($request->user())->allows('viewRole', [$this->resource, $role]);
                })->values(),

                // TODO: maybe round this to nearest day to minimize privacy issues:
                'created_at' => $this->created_at,
            ];
        }

        if (Gate::forUser($request->user())->allows('viewContentScramblerInfo', $this->resource)) {
            // Expose content scrambling information:
            $data['content_scrambler_algorithm'] = $this->content_scrambler_algorithm;
        }

        return $data;
    }
}
