<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
$this->app->router->group(
    [
        'prefix' => 'api',
        // 'namespace' => 'lumilock\lumilock\App\Http\Controllers'
    ],
    function ($router) {
        $router->get('/test', function () {
            return "hello";
        });
    }
);
