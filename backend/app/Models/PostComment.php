<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;

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
    /** Get the user that made this comment. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
