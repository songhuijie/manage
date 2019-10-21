<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    /**
     * 用户账号，密码登录
     */
    $router->post('auth/verify/login', 'Auth\AuthController@verifyLogin')->name('login.first');
    $router->get('auth/login', 'Auth\AuthController@getLogin');
    $router->post('auth/login', 'Auth\AuthController@postLogin');
    $router->get('auth/logout', 'Auth\AuthController@getLogout');
});
