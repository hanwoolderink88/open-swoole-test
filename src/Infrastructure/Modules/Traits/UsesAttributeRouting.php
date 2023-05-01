<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Modules\Traits;

use HaydenPierce\ClassFinder\ClassFinder;
use ReflectionClass;
use ReflectionMethod;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Middleware as MiddlewareAttribute;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Route as RouteAttribute;
use User\Swoole\Infrastructure\Http\Routing\Router;

trait UsesAttributeRouting
{
    private function initRoutesFromController(Router $router, string $controllerNamespace): void
    {
        $routes = [];

        ClassFinder::disablePSR4Vendors();
        $classes = ClassFinder::getClassesInNamespace($controllerNamespace, ClassFinder::RECURSIVE_MODE);

        foreach ($classes as $class) {
            $reflectionClass = new ReflectionClass($class);
            $classMiddlewares = $reflectionClass->getAttributes(MiddlewareAttribute::class);

            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $routeAttribute = $method->getAttributes(RouteAttribute::class);

                if (empty($routeAttribute)) {
                    continue;
                }

                $routeAttribute = $routeAttribute[0]->newInstance();

                $controller = [$class, $method->getName()];

                $route = $router->addRoute($routeAttribute->method, $routeAttribute->path, $controller);

                $methodMiddleWares = $method->getAttributes(MiddlewareAttribute::class);

                $middlewares = array_unique(array_merge($classMiddlewares, $methodMiddleWares));

                foreach ($middlewares as $middleware) {
                    $middleware = $middleware->newInstance();

                    $route->addMiddleware($middleware->middleware);
                }
            }
        }
    }
}