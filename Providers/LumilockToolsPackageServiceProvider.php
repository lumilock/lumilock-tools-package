<?php

namespace lumilock\lumilockToolsPackage\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use lumilock\lumilockToolsPackage\App\Http\Middleware\Authenticate;
use lumilock\lumilockToolsPackage\App\Http\Middleware\AuthenticateAccessMiddleware;

class LumilockToolsPackageServiceProvider extends ServiceProvider
{

   /**
    * Bootstrap the application services.
    *
    * @return void
    */
   public function boot()
   {
      // Global Middleware
      $kernel = $this->app->make(Kernel::class);
      $kernel->pushMiddleware(AuthenticateAccessMiddleware::class);
      
      // Route Middleware
      $router = $this->app->make(Router::class);
      $router->aliasMiddleware('auth', Authenticate::class);

      $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
      $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
   }

   /**
    * Register the application services.
    *
    * @return void
    */
   public function register()
   {
      // Register all the Package's Service Providers
      $this->app->register(
         LumilockAuthServiceProvider::class,
      );

      $configPath = __DIR__ . '/../config/auth.php';
      $this->mergeConfigFrom($configPath, 'auth');

      $baseConfigPath = base_path() . '/config';
      if (!file_exists($baseConfigPath)) {
         mkdir($baseConfigPath, 0777, true);
      }
      // Will copy package/auth.php to project/auth.php
      // overwritting it if necessary
      copy($configPath, $baseConfigPath . '/auth.php');
   }

   /**
    * Get the services provided by the provider.
    *
    * @return array
    */
   public function provides()
   {
      return ['lumilock'];
   }
}
