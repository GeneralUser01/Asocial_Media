<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\Role;
use App\Policies\PostCommentPolicy;
use App\Policies\PostPolicy;
use App\Policies\RolePolicy;
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
    }
}
