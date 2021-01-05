<?php

namespace lumilock\lumilockToolsPackage\App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->provider('custom', function () {
            return new CustomUserProvider($this->app['hash'], $this->app['config']['auth.providers.custom']['model'], $this->app['session.store']);
        });
        $this->app['auth']->extend('GuardToken', function(){
            $provider = $this->app['auth']->createUserProvider($this->app['config']['auth.guards.web']['provider']);
            $guard = new CheckTokenGuard($provider, $this->app['session.store']);
          return $guard;
      });
    }
}
