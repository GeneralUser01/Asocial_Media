<?php

namespace App\Policies;

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
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(?User $user)
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
    public function view(?User $user, PostComment $comment)
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
    public function create(User $user)
    {
        // "user" isn't optional so they are logged in.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PostComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, PostComment $comment)
    {
        // Can edit your own comments.
        //
        // For more info see:
        // https://laravel.com/docs/8.x/authorization#policy-responses

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
    public function delete(User $user, PostComment $comment)
    {
        // We don't support deleting comments yet. That might change what page
        // other comments are at.
        return Response::deny("Deleting comments is currently not supported");
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
