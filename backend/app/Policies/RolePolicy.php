<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    use HandlesAuthorization;

    /** Determine whether the user can see all users with a specific role.
     *
     * For showing the roles of a single user, please see
     * `UserPolicy::viewRoles`.
     */
    public function showUserRoles(?User $user, Role $role)
    {
        if ($user->isAdministrator()) {
            return Response::allow();
        }

        if ((!$role->isRestriction()) && $user && $user->hasRole($role->id)) {
            // Can see others that have the same role as yourself (unless the
            // role was a punishment):
            return Response::allow();
        }

        return Response::deny("You can't see all users that have this role.");
    }
    /** Add a new role to a user. */
    public function addUserRole(User $user, Role $role, User $affectedUser)
    {
        if ($user->isAdministrator()) {
            return Response::allow();
        }

        if ($user->isDisabled()) {
            return Response::deny("You are disabled and can't do anything");
        }

        // Only admins
        return Response::deny("You can't add roles to users.");
    }
    /** Remove a role from a user. */
    public function removeUserRole(User $user, Role $role, User $affectedUser)
    {
        if ($user->isAdministrator()) {
            return Response::allow();
        }

        if ($role->isRestriction()) {
            return Response::deny("You cannot remove a restriction from yourself.");
        }

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
        if ($user->isAdministrator()) {
            return Response::allow();
        }

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
        if ($user->isAdministrator()) {
            if ($role->isHardcoded()) {
                Response::deny("You can't change a hardcoded role.");
            }
            return Response::allow();
        }

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
        if ($user->isAdministrator()) {
            if ($role->isHardcoded()) {
                Response::deny("You can't delete a hardcoded role.");
            }
            return Response::allow();
        }

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
