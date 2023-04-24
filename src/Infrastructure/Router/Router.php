<?php

namespace User\Swoole\Infrastructure\Router;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Throwable;
use User\Swoole\Infrastructure\Container\Application;

class Router
{
    private RouteCollection $routes;

    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): static
    {
        if (self::$instance == null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function route(Application $app): Response
    {
        $request = $app->make(Request::class);

        $context = new RequestContext(
            $request->getUri()->getPath(),
            $request->getMethod(),
        );

        if (!isset($this->routes)) {
            echo 'init routes' . PHP_EOL;
            $this->initRoutes();
        }

        try {
            $matcher = new UrlMatcher($this->routes, $context);
            $parameters = $matcher->match($request->getUri()->getPath());

            $controller = $parameters['callable'][0];
            $method = $parameters['callable'][1];

            $additionalMethodParams = $parameters;
            unset($additionalMethodParams['callable']);
            unset($additionalMethodParams['_route']);

            return $app->call($controller . '@' . $method, $additionalMethodParams);
        } catch (ResourceNotFoundException) {
            return new Response(404, [], 'Not Found');
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function initRoutes(): void
    {
        $this->routes = new RouteCollection();

        $addRoute = function (string $name, string $path, string $controller, string $method) {
            $this->routes->add($name, new Route($path, ['callable' => [$controller, $method]]));
        };

        require __DIR__ . '/../../../routes/routes.php';
    }

    /**
     * @param string $name
     * @param string $path
     * @param array $controller
     * @return void
     */
    public static function get(string $name, string $path, array $controller): void
    {
        $route = new Route(
            path: $path,
            defaults: ['callable' => $controller],
            methods: 'GET'
        );

        self::getInstance()->routes->add($path, $route);
    }
}
