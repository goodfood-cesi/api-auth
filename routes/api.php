<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['middleware' => 'auth'], function ($router) {
    $router->get('user', ['as' => 'users.me', 'uses' => 'AuthController@user']);
    $router->post('user', ['as' => 'users.edit', 'uses' => 'AuthController@edit']);
    $router->post('logout',  ['as' => 'users.logout', 'uses' => 'AuthController@logout']);
});

$router->post('refresh', ['as' => 'token.refresh', 'uses' => 'AuthController@refresh']);
$router->post('login',  ['as' => 'users.login', 'uses' => 'AuthController@login']);
$router->post('register',  ['as' => 'users.register', 'uses' => 'AuthController@register']);
$router->post('forgot-password',  ['as' => 'users.forgot_password', 'uses' => 'AuthController@forgot_password']);
$router->post('password', ['as' => 'users.password', 'uses' => 'AuthController@password']);
