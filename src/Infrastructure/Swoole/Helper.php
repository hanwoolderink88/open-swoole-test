<?php

namespace User\Swoole\Infrastructure\Swoole;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use OpenSwoole\Http\Response as SwooleResponse;
use OpenSwoole\HTTP\Server as SwooleServer;
use User\Swoole\Infrastructure\Container\Application;

class Helper
{
    protected float $startTime;

    public function __construct()
    {
        $this->startTime = floor(microtime(true) * 1000);
    }

    public function createPrsRequest($request): Request
    {
        $psr17Factory = new Psr17Factory();
        $psrRequest = $psr17Factory->createRequest(
            $request->server['request_method'],
            $request->server['request_uri'] . '?' . ($request->server['query_string'] ?? ''),
        );

        foreach ($request->header as $key => $value) {
            $psrRequest = $psrRequest->withHeader($key, $value);
        }

        if (isset($request->cookie) && is_array($request->cookie)) {
            foreach ($request->cookie as $key => $value) {
                $psrRequest = $psrRequest->withAddedHeader('Cookie', $key . '=' . $value);
            }
        }

        $psrRequest->getBody()->write($request->rawContent());

        return $psrRequest;
    }

    public function updateResponse(SwooleResponse $response, Response $psrResponse): void
    {
        $response->header("content-type", $psrResponse->getHeader('content-type') ?? 'text/html');
        $response->end($psrResponse->getBody()->getContents());
    }

    public function handleStatic(mixed $uri, SwooleResponse $response): void
    {
        $location = __DIR__ . '/../../../resources/' . ltrim($uri);

        if (file_exists($location)) {
            $file = file_get_contents($location);

            $file_extension = pathinfo($uri, PATHINFO_EXTENSION);
            $response->status(200);
            $response->header('Content-Type', match ($file_extension) {
                'pdf' => 'application/pdf',
                'zip' => 'application/zip',
                'doc' => 'application/msword',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',
                'gif' => 'image/gif',
                'png' => 'image/png',
                'jpeg', 'jpg' => 'image/jpg',
                'ico' => 'image/x-icon',
                default => 'application/octet-stream',
            });
            $response->end($file);
        } else {
            $response->status(404);
            $response->end();
        }
    }

    // todo: works for now, but not the best solution
    public function shouldReload(SwooleServer $server): void
    {
        $dev = true;

        if ($dev) {
            $sha = file_exists(__DIR__ . '/../../../sha') ? file_get_contents(__DIR__ . '/../../../sha') : null;
            $newSha = file_exists(__DIR__ . '/../../../shaNew') ? file_get_contents(__DIR__ . '/../../../shaNew') : null;

            if ($sha !== $newSha) {
                $file = fopen(__DIR__ . '/../../../sha', 'wa+');
                fwrite($file, $newSha);

                $server->reload(false);
            }
        }
    }

    public function isStatic(mixed $uri): bool
    {
        $folderRegex = '/^\/(css|js|img|fonts)\//';
        $extensionRegex = '/\.(css|js|png|jpg|jpeg|gif|ico|pdf|zip|doc|xls|ppt)$/';

        return preg_match($folderRegex, $uri) || preg_match($extensionRegex, $uri);
    }

    public function getTime(): int
    {
        return floor(microtime(true) * 1000) - $this->startTime;
    }

    public function log(Application $app, Response $response): string
    {
        $request = $app->make(Request::class);

        return $this->getTime() . 'ms '
        . $response->getStatusCode() . ' '
        . $request->getMethod() . '  '
        . $request->getUri()->getPath() . PHP_EOL;
    }
}
