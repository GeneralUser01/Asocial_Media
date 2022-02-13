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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'entry',
        'entry_id',
    ];

    /**
     * All of the relationships to be touched. (Sync "updated_at" timestamp)
     *
     * @var array
     */
    protected $touches = ['entry'];

    /**
     * Get the "entry" that owns this model.
     */
    public function entry()
    {
        return $this->hasOne(Entry::class);
    }

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
