<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Modules;

use User\Swoole\Infrastructure\Http\Routing\Router;

interface ModuleInterface
{
    public function routes(Router $router): void;

    public function container(): void;
}