<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Swoole;

use Nyholm\Psr7\Factory\Psr17Factory;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\HTTP\Server as SwooleServer;
use User\Swoole\Infrastructure\Container\Application;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\Response;

class Helper
{
    protected float $startTime;

    public function __construct(private string $basePath)
    {
        $this->startTime = floor(microtime(true) * 1000);
    }

    public function createRequest(SwooleRequest $request): Request
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

        return Request::createFromPsrRequest($psrRequest);
    }

    public function updateResponse(SwooleResponse $response, Response $psrResponse): void
    {
        $response->header("content-type", $psrResponse->getHeader('content-type') ?? 'text/html');
        $response->end($psrResponse->getBody()->getContents());
    }

    public function handleStatic(mixed $uri, SwooleResponse $response): void
    {
        $location = $this->basePath . '/resources/' . ltrim($uri);

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

    public function isStatic(mixed $uri): bool
    {
        $folderRegex = '/^\/(css|js|img|fonts)\//';
        $extensionRegex = '/\.(css|js|png|jpg|jpeg|gif|ico|pdf|zip|doc|xls|ppt)$/';

        return preg_match($folderRegex, $uri) || preg_match($extensionRegex, $uri);
    }

    public function getTime(): float
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
