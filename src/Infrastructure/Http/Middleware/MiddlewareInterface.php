<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Http\Middleware;

use User\Swoole\Infrastructure\Http\Request\Request;

interface MiddlewareInterface
{
    public function handle(Request $request): void;
}