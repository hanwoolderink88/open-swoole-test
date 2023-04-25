<?php
declare(strict_types=1);

namespace User\Swoole\Application\Middleware;

use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Middleware\MiddlewareInterface;

class TestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {

    }
}