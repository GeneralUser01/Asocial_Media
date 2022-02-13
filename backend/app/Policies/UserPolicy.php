<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class UserPolicy
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

    /** Determine whether the user can view the email of a specific user.
     *
     * If `viewAllInfo` is allowed then this won't be checked and will be
     * assumed to allow as well.
     */
    public function viewEmail(?User $user, User $model)
    {
        if ($model->id === optional($user)->id) {
            // Can see your own email:
            return Response::allow();
        }

        // Could have setting for each user if they want to make the email public.
        return Response::deny("You can't view the email of this user.");
    }

    /** Determine whether the user can view the content scrambling information
     * of a specific user.
     *
     * This will be checked even if `viewAllInfo` is allowed since this info can
     * be hidden even from the users themselves.
     */
    public function viewContentScramblerInfo(User $user, User $model)
    {
        // Only admins can see that we are messing with the users' content.
        return Response::deny("You can't view any information about how content is processed.");
    }

    /** Determine whether the user can view a specific role for a specific user.
     *
     * If `viewAllInfo` is allowed then this won't be checked and will be
     * assumed to allow as well.
     */
    public function viewRole(?User $user, User $model, Role $role)
    {
        if ($model->id === optional($user)->id) {
            // Can see your own roles:
            return Response::allow();
        }
        if (Gate::forUser($user)->allows('showUserRoles', $role)) {
            // If `RolePolicy::showsUserRoles` is allowed then the user can
            // already determine all users with this role, so allow it here as
            // well:
            return Response::allow();
        }
        return Response::deny("You can't view some roles for this user.");
    }

    /** Determine whether the user can view all info for a specific user. If
     * they can then they will access the same info as the user themselves
     * would. */
    public function viewAllInfo(?User $user, User $model)
    {
        if ($model->id === optional($user)->id) {
            // Can see all info about yourself:
            return Response::allow();
        }
        return Response::deny("You can only see some info about this user.");
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(?User $user)
    {
        // Anyone can get a list of our users.
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, User $model)
    {
        // Anyone can view a specific user.
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(?User $user)
    {
        // Not in use.
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, User $model)
    {
        // Not in use.
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, User $model)
    {
        // Note: admins can delete all accounts.

        // Users are allowed to delete their own accounts:
        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('You can not delete other users.');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, User $model)
    {
        // Not in use.
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, User $model)
    {
        // Not in use.
    }
}
