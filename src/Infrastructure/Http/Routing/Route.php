<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Http\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;

class Route extends SymfonyRoute
{
    public function addMiddleWare(string $middleware): static
    {
        $middlewares = $this->hasDefault('_middleware')
            ? [...$this->getDefault('_middleware'), $middleware]
            : [$middleware];

        $this->setDefault('_middleware', $middlewares);

        return $this;
    }
}