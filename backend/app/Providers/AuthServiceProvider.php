<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\Role;
use App\Models\User;
use App\Policies\PostCommentPolicy;
use App\Policies\PostPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Config;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        //
        // These can be auto discovered but we try to be explicit. For more info
        // see: https://laravel.com/docs/8.x/authorization#registering-policies
        Post::class => PostPolicy::class,
        PostComment::class => PostCommentPolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // A link with a URL of this type will be emailed to a user when they
        // request a password reset. Our frontend will then show them a form
        // where they can specify a new password.
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return Config::get('app.url') . '/reset-password?token=' . $token;
        });

        // If we want to automatically generate rows for a `permissions` DB
        // table based on our policies then here are some things to keep in
        // mind:
        // - `Gate::getPolicyFor($arg)` where $arg is a model object (`new
        //   User()`) or object class (`User::class`) returns a policy object
        //   for that model.
        // - `Gate::policies()` returns the policies stored inside the gate,
        //   this array will have the same structure as the `$policies` array
        //   inside this provider.
        //   - Note that other policy classes might still be guessed at runtime
        //     so this might not be all policies unless we manually list all of
        //     them in the `$policies` array inside this provider.
        // - `Gate::abilities()` returns an array with gate names as keys and
        //   callbacks as values.

    }
}
