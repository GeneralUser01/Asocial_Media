<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content'];

    /** Get the post for which this comment was made. */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
