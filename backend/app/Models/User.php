<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable // implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['roles'];


    /** Used to automatically add the "roles" field/attribute to serialized
     * responses.
     *
     * For more info see:
     * https://laravel.com/docs/8.x/eloquent-serialization#appending-values-to-json
     */
    public function getRolesAttribute()
    {
        return $this->roles()->get(['id']);
    }

    /** Get the posts that this user has made. */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    /** Get the comments that this user has made. */
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    /** Get the roles that this user has. */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole($roleId)
    {
        // This was inspired by code from:
        // https://stackoverflow.com/questions/24555697/check-if-belongstomany-relation-exists-laravel
        return $this->roles()->where('id', $roleId)->exists();
    }
    public function hasNamedRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }
    /** Check if this user has the administrator role. */
    public function isAdministrator()
    {
        return $this->hasNamedRole('Administrator');
    }
}
