<?php

namespace App\Policies;

use App\Models\Like;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PostCommentPolicy
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
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewLikes(?User $user, PostComment $comment)
    {
        // Anyone cam see likes:
        return true;
    }

    /**
     * Determine whether the user can see dislikes for the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewDislikes(?User $user, PostComment $comment)
    {
        // Anyone can see dislikes:
        return true;
    }

    /**
     * Determine whether the user can like the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function like(User $user, PostComment $comment, Post $post)
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
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function dislike(User $user, PostComment $comment, Post $post)
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
     * @param  \App\Models\PostComment  $comment
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function unlike(User $user, PostComment $comment, Post $post, Like $like)
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
    public function viewAny(?User $user, Post $post)
    {
        // Anyone, even guests, can view comments.

        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, PostComment $comment, Post $post)
    {
        // Anyone, even guests, can view comments.

        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Post $post)
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
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, PostComment $comment, Post $post)
    {
        // Can edit your own comments.
        //
        // For more info see:
        // https://laravel.com/docs/8.x/authorization#policy-responses

        if ($user->isDisabled()) {
            return Response::deny("You are disabled and can't do anything");
        }

        return $user->id === $comment->user_id
            ? Response::allow()
            : Response::deny('You do not own this comment.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, PostComment $comment, Post $post)
    {
        if ($user->isDisabled()) {
            return Response::deny("You are disabled and can't do anything");
        }

        return $user->id === $comment->user_id
        ? Response::allow()
        : Response::deny('You do not own this comment.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, PostComment $comment)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, PostComment $comment)
    {
        //
    }
}
