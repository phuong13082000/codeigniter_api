<?php

namespace Config;

use App\Controllers\AuthController;
use App\Controllers\PropertyController;

$routes = Services::routes();

$routes->setDefaultNamespace('App\Controllers');

$routes->group('api/v1', function ($routes) {
    $routes->get('properties', [PropertyController::class, 'index']);
    $routes->resource('rooms', ['controller' => 'RoomController', 'only' => ['index','show']]);

    $routes->group('', ['filter' => 'jwt'], function($routes){
        $routes->resource('bookings', ['controller' => 'BookingController', 'only' => ['create','index','show']]);
    });

    $routes->group('user', function ($routes) {
        $routes->post('register', [AuthController::class, 'register']);
        $routes->post('login', [AuthController::class, 'login']);
        $routes->post('refresh', [AuthController::class, 'refresh']);
        $routes->post('logout', [AuthController::class, 'logout'], ['filter' => 'jwt']);
    });
});