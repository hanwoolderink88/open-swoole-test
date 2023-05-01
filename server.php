<?php
declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Hanwoolderink\Data\Dto\Data;
use Illuminate\Validation\ValidationException;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\HTTP\Server as SwooleServer;
use User\Swoole\Infrastructure\Container\Application;
use User\Swoole\Infrastructure\Http\Exceptions\HttpException;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\Response;
use User\Swoole\Infrastructure\Http\Routing\Router;
use User\Swoole\Infrastructure\Http\Routing\RouterFactory;
use User\Swoole\Infrastructure\Modules\Kernel;
use User\Swoole\Infrastructure\Persistence\EntityManagerFactory;
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

    if ($helper->isStatic($request->server['request_uri'])) {
        $helper->handleStatic($request->server['request_uri'], $response);

        return;
    }

    $request = $helper->createRequest($request);

    $kernel = new Kernel(__DIR__);
    $appResponse = $kernel->handle($request);

    $helper->updateResponse($response, $appResponse);

    // goes to stdout
    echo $helper->log($kernel->getApp(), $appResponse);
});

$server->set([
    'worker_num' => 1,
]);

$server->start();
