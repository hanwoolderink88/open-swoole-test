<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Http\Routing;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Throwable;
use User\Swoole\Infrastructure\Container\Application;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\Response;

class Router
{
    private RouteCollection $routes;

    public function route(Application $app): Response
    {
        $request = $app->make(Request::class);

        $context = new RequestContext(
            $request->getUri()->getPath(),
            $request->getMethod(),
        );

        try {
            $matcher = new UrlMatcher($this->routes, $context);
            $parameters = $matcher->match($request->getUri()->getPath());

            $matcher->getContext();

            $this->callMiddlewares($app, $parameters['_middleware'] ?? []);

            $controller = $parameters['callable'][0];
            $method = $parameters['callable'][1];

            $additionalMethodParams = $parameters;
            unset($additionalMethodParams['callable']);
            unset($additionalMethodParams['_route']);
            unset($additionalMethodParams['_middleware']);

            return $app->call($controller . '@' . $method, $additionalMethodParams);
        } catch (ResourceNotFoundException) {
            return new Response(404, [], 'Not Found');
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function initRoutes(string $basePath): void
    {
        echo 'init routes' . PHP_EOL;

        $this->routes = new RouteCollection();

        // used in the routes.php file
        $router = $this;

        require $basePath . '/routes/routes.php';
    }

    public function addRoute(string $method, string $path, array $controller): Route
    {
        $route = new Route(
            path: $path,
            defaults: ['callable' => $controller],
            methods: $method,
        );

        $this->routes->add($path, $route);

        return $route;
    }

    private function callMiddlewares(Application $app, array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $app->call($middleware . '@handle');
        }
    }
}
