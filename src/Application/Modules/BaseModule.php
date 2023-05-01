<?php
declare(strict_types=1);

namespace User\Swoole\Application\Modules;

use User\Swoole\Infrastructure\Container\Application;
use User\Swoole\Infrastructure\Http\Routing\Router;
use User\Swoole\Infrastructure\Modules\ModuleInterface;
use User\Swoole\Infrastructure\Modules\Traits\UsesAttributeRouting;

class BaseModule implements ModuleInterface
{
    use UsesAttributeRouting;

    public function __construct(private Application $app)
    {
    }

    public function routes(Router $router): void
    {
        $this->initRoutesFromController($router, 'User\Swoole\Domain\Controllers');
    }

    public function container(): void
    {
        // add all bindings here...
    }
}