<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'body'];

    /** Get the comments for this post. */
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }
}
