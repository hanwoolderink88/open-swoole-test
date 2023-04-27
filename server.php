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

    $uri = $request->server['request_uri'];

    if ($helper->isStatic($uri)) {
        $helper->handleStatic($uri, $response);

        return;
    }

    $app = Application::getInstance();
    $app->setBasePath(__DIR__);

    $app->singletonIf(Router::class, function (Application $app) {
        return RouterFactory::create();
    });

    $app->singletonIf(EntityManager::class, function (Application $app) {
        return EntityManagerFactory::create();
    });

    $app->singleton(Request::class, function (Application $app) use ($helper, $request) {
        return $helper->createRequest($request);
    });

    $app->afterResolving(Data::class, function (Data $abstract, Application $app) {
        /** @var Request $request */
        $request = $app->make(Request::class);

        return $abstract->setData($request->get())->validate();
    });

    $router = $app->make(Router::class);

    try {
        $appResponse = $router->route($app);
    } catch (HttpException $e) {
        $appResponse = new Response(
            $e->getCode(),
            [
                'Content-Type' => 'application/json',
            ],
            json_encode([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ])
        );
    } catch (ValidationException $e) {
        $appResponse = new Response(
            422,
            [
                'Content-Type' => 'application/json',
            ],
            json_encode([
                'code' => 422,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ])
        );
    }


    $helper->updateResponse($response, $appResponse);

    // goes to stdout
    echo $helper->log($app, $appResponse);
});

$server->set([
    'worker_num' => 1,
]);

$server->start();
