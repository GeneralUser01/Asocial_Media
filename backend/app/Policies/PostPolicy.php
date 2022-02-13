<?php

namespace App\Policies;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        // For more info see:
        // https://laravel.com/docs/8.x/authorization#policy-filters

        if ($user->isAdministrator()) {
            // Admins can do anything
            return true;
        }
        // If this returns null then the normal method is used.
    }

    /**
     * Determine whether the user can see likes for the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewLikes(?User $user, Post $post)
    {
        // Anyone cam see likes:
        return true;
    }

    /**
     * Determine whether the user can see dislikes for the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewDislikes(?User $user, Post $post)
    {
        // Anyone can see dislikes:
        return true;
    }

    /**
     * Determine whether the user can like the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function like(User $user, Post $post)
    {
        if ($user->isDisabled()) {
            return Response::deny("You are disabled and can't do anything");
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can dislike the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function dislike(User $user, Post $post)
    {
        if ($user->isDisabled()) {
            return Response::deny("You are disabled and can't do anything");
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can remove their like or dislike from the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function unlike(User $user, Post $post, Like $like)
    {
        if ($user->isDisabled()) {
            return Response::deny("You are disabled and can't do anything");
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(?User $user)
    {
        // Anyone, even guests, can view posts.

        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, Post $post)
    {
        // Anyone, even guests, can view posts.
        //
        // For more info about guests see:
        // https://laravel.com/docs/8.x/authorization#guest-users

        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // "user" isn't optional so they are logged in.
        if ($user->isDisabled()) {
            return Response::deny("You are disabled and can't do anything");
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Post $post)
    {
        // Can edit your own posts.
        //
        // For more info see:
        // https://laravel.com/docs/8.x/authorization#policy-responses

        if ($user->isDisabled()) {
            return Response::deny("You are disabled and can't do anything");
        }

        return $user->id === $post->user_id
            ? Response::allow()
            : Response::deny('You do not own this post.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Post $post)
    {
        // Only admins can delete posts since that would delete all comments as
        // well.

        if ($user->isDisabled()) {
            return Response::deny("You are disabled and can't do anything");
        }

        // Could support deleting posts without comments:
        // if (! $post->comments()->exists()) { return Response::allow(); }

        // Users are allowed to delete their own posts:
        return $user->id === $post->user_id
            ? Response::allow()
            : Response::deny('You do not own this post.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Post $post)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Post $post)
    {
        //
    }
}
