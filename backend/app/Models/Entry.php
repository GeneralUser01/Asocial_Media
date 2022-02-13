<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entries';

    public static function createForModel(Model $data)
    {
        $entry = new Entry();

        // Handle different cases.
        //
        // See:
        // https://stackoverflow.com/questions/30331322/how-can-i-check-if-a-object-is-an-instance-of-a-specific-class

        if (!is_object($data)) {
            throw new \Exception("Must specify a model when creating an entry");
        } else if ($data instanceof User) {
            $entry->user()->associate($data);
        } else if ($data instanceof Post) {
            $entry->post()->associate($data);
        } else if ($data instanceof PostComment) {
            $entry->postComment()->associate($data);
        } else if ($data instanceof Like) {
            $entry->like()->associate($data);
        } else if ($data instanceof Role) {
            $entry->role()->associate($data);
        } else {
            throw new \Exception("Can't create Entry for model of type: " . get_class($data));
        }

        $entry->save();

        return $entry;
    }
    public static function createForUser(?User $user, Model $data)
    {
        $entry = Entry::createForModel($data);

        if ($user !== null) {
            // Add this entry to the user's action relation. For more info see:
            // https://laravel.com/docs/9.x/eloquent-relationships#attaching-detaching
            $user->actions()->attach($entry->id);
        }

        return $entry;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function postComment()
    {
        return $this->belongsTo(PostComment::class);
    }
    public function like()
    {
        return $this->belongsTo(Like::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    /** The user that created this entry. */
    public function userThatCreatedEntry()
    {
        // For info about hasOneThrough see:
        // https://laravel.com/docs/9.x/eloquent-relationships#has-one-through
        //
        // For more info about how this gets a single result from a many-to-many
        // relation see:
        // https://stackoverflow.com/questions/55591571/laravel-belongstomany-relationship-with-only-one-result/66969048#66969048
        return $this->hasOneThrough(
            User::class, // The Model returned by a successful query
            UserAction::class, // Model we go through to get a User
            'entry_id', // 1. (key on: UserAction)
            'id', // 2. (key on: User)
            'id', // 3. (key on: Entry) (matches against arg: 1)
            'user_id' // 4. (key on: UserAction) (matches against arg: 2)
        );

        // This is the real relationship, but this will only ever have one result so we the code above instead.
        // return $this->belongsToMany(User::class, 'user_actions', 'entry_id', 'user_id')->using(UserAction::class);
    }
    /** The likes or dislikes for this entry. */
    public function likes()
    {
        return $this->hasMany(Like::class, 'likeable_id');
    }
}
