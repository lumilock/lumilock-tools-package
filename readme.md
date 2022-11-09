# Lumilock-tools-package
## ⚠️ This repository is deprecated go to [lumilock-idp](https://github.com/lumilock/lumilock-idp) ⚠️
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Dev Version on Packagist][ico-version-dev]][link-packagist]

## 📚 Installation

Make some changes inside `bootstrap/app.php`.
```php
//After
// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);
```

```php
 // Add these lines
$app->register(lumilock\lumilockToolsPackage\Providers\LumilockToolsPackageServiceProvider::class);
```

Add AuthenticateAccessMiddleware Middleware to `bootstrap/app.php`.
```php
  $app->middleware([
    \lumilock\lumilockToolsPackage\App\Http\Middleware\AuthenticateAccessMiddleware::class
]);
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
