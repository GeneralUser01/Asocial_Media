<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'body'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['image', 'image_mime_type'];

    /** Get the comments for this post. */
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }
    /** Get the user that made this post. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
