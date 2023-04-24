<?php

use User\Swoole\Application\Middleware\TestMiddleware;
use User\Swoole\Domain\Controllers\TestController;
use User\Swoole\Domain\Controllers\UserController;
use User\Swoole\Infrastructure\Http\Routing\Router;

/** @var Router $router */

$router->addRoute('GET', '/', [TestController::class, 'home'])->addMiddleWare(TestMiddleware::class);
$router->addRoute('GET', '/about', [TestController::class, 'about']);
$router->addRoute('GET', '/users', [UserController::class, 'index']);
$router->addRoute('GET', '/users/{userId}', [UserController::class, 'show']);
