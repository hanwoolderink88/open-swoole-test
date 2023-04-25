<?php

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\HTTP\Server as SwooleServer;
use User\Swoole\Infrastructure\Container\Application;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Routing\Router;
use User\Swoole\Infrastructure\Swoole\Helper;

$server = new SwooleServer('0.0.0.0', 9501);

$server->on('start', function (SwooleServer $server) {
    echo 'Server started at http://0.0.0.0:9501' . PHP_EOL;
});

$server->on('workerStart', function (SwooleServer $server, int $workerId) {
    require __DIR__ . '/vendor/autoload.php';
});

$server->on('request', function (SwooleRequest $request, SwooleResponse $response) use ($server) {
    $helper = new Helper(__DIR__);

    $uri = $request->server['request_uri'];

    if ($helper->isStatic($uri)) {
        $helper->handleStatic($uri, $response);

        return;
    }

    $app = Application::getInstance();
    $app->setBasePath(__DIR__);

    $app->singletonIf(Router::class, function (Application $app) {
        $router = new Router();
        $router->initRoutes($app->getBasePath());

        return $router;
    });

    $app->singleton(Request::class, function (Application $app) use ($helper, $request) {
        return $helper->createRequest($request);
    });

    $router = $app->make(Router::class);
    $appResponse = $router->route($app);

    $helper->updateResponse($response, $appResponse);

    // goes to stdout
    echo $helper->log($app, $appResponse);
});

$server->set([
    'worker_num' => 1,
]);

$server->start();
