<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'is_like',
    ];

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

    /**
     * Get the "entry" that was liked or disliked.
     */
    public function likeable()
    {
        return $this->belongsTo(Entry::class, 'likeable_id');
    }
    /** Get the user that expressed their like or dislike. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
