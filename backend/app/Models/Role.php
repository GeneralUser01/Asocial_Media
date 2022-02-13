<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pivot'
    ];

    // Create roles with these names inside "database\seeders\DefaultRoleTableSeeder.php":
    public const ADMIN = "Administrator";
    public const DISABLED = "Disabled";

    /** `true` if this role represents a restriction in what a user can do.
     *
     * "restriction" roles have some special behavior such as:
     * - These roles are not automatically visible to other user that share the
     *   role.
     * - You cannot automatically remove these roles from yourself.
     */
    public function isRestriction() {
        return $this->isDisableRole();
    }
    /** `true` if this role is hardcoded and should always exist. */
    public function isHardcoded() {
        return $this->isDisableRole() || $this->isAdministratorRole();
    }

    public function isDisableRole() {
        return $this->name === Role::DISABLED;
    }
    public function isAdministratorRole() {
        return $this->name === Role::ADMIN;
    }

    /** Get the users with this role. */
    public function users() {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
