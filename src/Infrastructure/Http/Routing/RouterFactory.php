<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Http\Routing;

use HaydenPierce\ClassFinder\ClassFinder;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\RouteCollection;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Middleware as MiddlewareAttribute;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Route as RouteAttribute;

class RouterFactory
{
    private Router $router;

    public function __construct()
    {
    }

    public static function create(): Router
    {
        $router = new Router();
        $router->setRoutes(new RouteCollection());

        $factory = new static();
        $factory->setRouter($router);

        $factory->initRoutes();

        return $router;
    }

    private function initRoutes(): void
    {
        $this->initRoutesFromController();
    }

    private function initRoutesFromController(): void
    {
        ClassFinder::disablePSR4Vendors();
        // todo: config..
        $classes = ClassFinder::getClassesInNamespace('User\Swoole\Domain\Controllers', ClassFinder::RECURSIVE_MODE);

        foreach ($classes as $class) {
            $reflectionClass = new ReflectionClass($class);

            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $routeAttribute = $method->getAttributes(RouteAttribute::class);

                if (empty($routeAttribute)) {
                    continue;
                }

                $routeAttribute = $routeAttribute[0]->newInstance();

                $controller = [$class, $method->getName()];

                $route = $this->router->addRoute($routeAttribute->method, $routeAttribute->path, $controller);

                $middlewares = $method->getAttributes(MiddlewareAttribute::class);

                foreach ($middlewares as $middleware) {
                    $middleware = $middleware->newInstance();

                    $route->addMiddleware($middleware->middleware);
                }
            }
        }
    }

    private function setRouter(Router $router): void
    {
        $this->router = $router;
    }
}