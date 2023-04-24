<?php

use User\Swoole\Domain\Controllers\TestController;
use User\Swoole\Domain\Controllers\UserController;
use User\Swoole\Infrastructure\Router\Router;

/** @var Closure $addRoute */

Router::get('home', '/', [TestController::class, 'home']);
Router::get('about', '/about', [TestController::class, 'about']);
Router::get('users.index', '/users', [UserController::class, 'index']);
Router::get('users.show', '/users/{userId}', [UserController::class, 'show']);
