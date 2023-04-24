<?php

use Nyholm\Psr7\Request;
use OpenSwoole\Http\Request as SwooleRequest;
use OpenSwoole\Http\Response as SwooleResponse;
use OpenSwoole\HTTP\Server as SwooleServer;
use User\Swoole\Infrastructure\Container\Application;
use User\Swoole\Infrastructure\Router\Router;
use User\Swoole\Infrastructure\Swoole\Helper;

require __DIR__ . '/vendor/autoload.php';

$server = new SwooleServer('127.0.0.1', 9501);

$server->on('start', function (SwooleServer $server) {
    echo 'Server started at http://127.0.0.1:9501' . PHP_EOL;
});

$server->on('request', function (SwooleRequest $request, SwooleResponse $response) use ($server) {
    $helper = new Helper();

    $helper->shouldReload($server);

    $uri = $request->server['request_uri'];

    if ($helper->isStatic($uri)) {
        $helper->handleStatic($uri, $response);

        return;
    }

    $app = new Application(__DIR__);

    $app->singleton(Request::class, function (Application $app) use ($helper, $request) {
        return $helper->createPrsRequest($request);
    });

    $router = Router::getInstance();
    $psrResponse = $router->route($app);

    $helper->updateResponse($response, $psrResponse);

    // goes to stdout
    echo $helper->log($app, $psrResponse);
});

$server->set([
    'worker_num' => 1,
]);

$server->start();
