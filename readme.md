# Lumilock-tools-package

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Dev Version on Packagist][ico-version-dev]][link-packagist]

## 📚 Installation

Make some changes inside `bootstrap/app.php`.

```php
 // Add these lines
$app->singleton(Illuminate\Session\SessionManager::class, function () use ($app) {
    return $app->loadComponent('session', Illuminate\Session\SessionServiceProvider::class, 'session');
});

$app->singleton('session.store', function () use ($app) {
    return $app->loadComponent('session', Illuminate\Session\SessionServiceProvider::class, 'session.store');
});
```
```php
 // Add these lines
$app->middleware([
    \lumilock\lumilockToolsPackage\App\Http\Middleware\AuthenticateAccessMiddleware::class
]);

$app->middleware([
    \lumilock\lumilockToolsPackage\App\Http\Middleware\Authenticate::class
]);

$app->middleware([
    \Illuminate\Session\Middleware\StartSession::class,
]);
```
```php
 // Add these lines
$app->register(lumilock\lumilockToolsPackage\Providers\LumilockToolsPackageServiceProvider::class);
$app->register(lumilock\lumilockToolsPackage\Providers\AuthServiceProvider::class);
```

create the storage session directory : 
```shell
mkdir -p storage/framework/sessions
```

Now for your Lumilock service app, inside the lumen .env file you can add the varible `ACCEPTED_SECRETS=` in order to create a secret key to protect your service. This secret_key need to be given to the lumilock_gateway.

## 📰 Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.


## 👨‍👩‍👧‍👦 Credits

- [lumilock-tools-package (Thibaud PERRIN)][link-author]


## 📝 License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/perrinthibaud/laravlock.svg
[ico-version-dev]: https://img.shields.io/packagist/vpre/perrinthibaud/laravlock.svg

[link-packagist]: https://packagist.org/packages/perrinthibaud/laravlock
[link-author]: https://github.com/lumilock
[link-contributors]: ../../contributors]