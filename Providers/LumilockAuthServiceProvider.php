<?php

namespace lumilock\lumilockToolsPackage\Providers;


use Illuminate\Support\Facades\Auth;
use lumilock\lumilockToolsPackage\App\Models\User;
use Illuminate\Support\ServiceProvider;
use lumilock\lumilockToolsPackage\App\Auth\CheckTokenGuard;
use lumilock\lumilockToolsPackage\App\Auth\CustomUserProvider;

class LumilockAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
      'App\Model' => 'App\Policies\ModelPolicy',
    ];
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('lumilock\lumilockToolsPackage\App\Models\Auth\User', function ($app) {
            return new User($app->make());
        });

        Auth::provider(
            'auth-provider',
            function ($app, array $config) {
                return new CustomUserProvider($app['hash'], $config['model'], $app['session.store']);
            }
        );

        Auth::extend('GuardToken', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
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
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        Auth::viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first(); // TODO if think that this function does not work
            }
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
       return ['lumilockAuth'];
    }
}
