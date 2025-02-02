<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // GATES
        Gate::define('isAdmin', function($user){
            return $user->type == 'admin';
        });

        Gate::define('isUser', function($user){
            return $user->type == 'user';
        });

        Gate::define('isAuthor', function($user){
            return $user->type == 'author';
        });

        Gate::define('isAdminOrAuthor', function($user){
            return $user->type == 'admin' || $user->type == 'author';
        });

        Gate::define('isMyAccount', function($user, $profileUser){
            return $user->id == $profileUser->id;
        });

        // Passport
        Passport::routes();
    }
}
