<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RolePolicy
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

    /** Determine whether the user can see all users with a specific role.
     *
     * For showing the roles of a single user, please see
     * `UserPolicy::viewRoles`.
     */
    public function showUserRoles(?User $user, Role $role)
    {
        if ($user && $user->hasRole($role->id)) {
            // Can see others that have the same role as yourself:
            return Response::allow();
        }
        return Response::deny("You can't see all users that have this role.");
    }
    /** Add a new role to a user. */
    public function addUserRole(User $user, Role $role, User $affectedUser)
    {
        // Only admins
        return Response::deny("You can't add roles to users.");
    }
    /** Remove a role from a user. */
    public function removeUserRole(User $user, Role $role, User $affectedUser)
    {
        return $user->id === $affectedUser->id
            ? Response::allow()
            : Response::deny('You can only remove roles from yourself.');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(?User $user)
    {
        // Anyone, even guests, can see what roles this server has.
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, Role $role)
    {
        // Anyone, even guests, can see info about a specific role.
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
        // Only admins can create new roles
        return Response::deny("You can't create new roles.");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Role $role)
    {
        // Only admins can change roles
        return Response::deny("You can't change existing roles.");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Role $role)
    {
        // Only admins can delete roles
        return Response::deny("You can't remove existing roles.");
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Role $role)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Role $role)
    {
        //
    }
}
