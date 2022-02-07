<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    // This controller manages the roles of users. This is a "many-to-many"
    // relationship since each user can have multiple roles.
    //
    // See:
    // - https://stackoverflow.com/questions/47552853/api-route-to-a-many-to-many-relationship
    // - https://stackoverflow.com/questions/24702640/laravel-save-update-many-to-many-relationship

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Role $role)
    {
        $this->authorize('showUserRoles', $role);

        // Show users that have this role:
        return $role->users()->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Role $role)
    {
        // Expect a user_id field in the request:
        $user = User::findOrFail($request->user_id);

        $this->authorize('addUserRole', [$role, $user]);

        // Add this role to the user:
        $user->roles()->sync([$role->id], false);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role, User $user)
    {
        // This method has been disabled in the route file.
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role, User $user)
    {
        // This method has been disabled in the route file.
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role, User $user)
    {
        $this->authorize('removeUserRole', [$role, $user]);

        // Remove this role form this user.
        //
        // See: https://www.amitmerchant.com/attach-detach-sync-laravel/
        $user->roles()->detach($role->id);
    }
}
