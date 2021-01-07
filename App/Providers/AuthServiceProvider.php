<?php

namespace lumilock\lumilockToolsPackage\App\Providers;

use Closure;
use lumilock\lumilockToolsPackage\App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use lumilock\lumilockToolsPackage\App\Auth\CheckTokenGuard;
use lumilock\lumilockToolsPackage\App\Auth\CustomUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        echo "- register \n";
        $this->app['auth']->provider(
            'auth-provider',
            function ($app, array $config) {
                echo "- register auth-provider \n";
                return new CustomUserProvider($app['hash'], $config['model'], $app['session.store']);
            }
        );

        $this->app['auth']->extend('GuardToken', function () {
            echo "- register GuardToken \n";
            $provider = $this->app['auth']->createUserProvider($this->app['config']['auth.guards.api']['provider']);
            $guard = new CheckTokenGuard($provider, $this->app['session.store']);
            return $guard;
        });
        
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        echo "- boot \n";
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        $this->app['auth']->viaRequest('api', function ($request) {
            echo "- boot api \n";
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first();
            }
        });
    }
}
