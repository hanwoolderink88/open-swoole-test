<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Http\Routing;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use HaydenPierce\ClassFinder\ClassFinder;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Throwable;
use User\Swoole\Infrastructure\Container\Application;
use User\Swoole\Infrastructure\Http\Exceptions\HttpException;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\JsonResponse;
use User\Swoole\Infrastructure\Http\Response\Response;
use User\Swoole\Infrastructure\Http\Response\ResponseInterface;

class Router
{
    private RouteCollection $routes;

    private array $bindingsClasses;

    public function route(Application $app): ResponseInterface
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

            $this->addBindings($app, $additionalMethodParams);

            return $app->call($controller . '@' . $method, $additionalMethodParams);
        } catch (ResourceNotFoundException) {
            throw new HttpException('Route does not exist', 404);
        } catch (MethodNotAllowedException) {
            throw new HttpException('Method not allowed', 405);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function callMiddlewares(Application $app, array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $app->call($middleware . '@handle');
        }
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    public function setRoutes(RouteCollection $routeCollection): void
    {
        $this->routes = $routeCollection;
    }

    public function addRoute(string $method, string $path, array $controller): Route
    {
        $route = new Route(
            path: $path,
            defaults: ['callable' => $controller],
            methods: $method,
        );

        $this->routes->add($path . ':' . $method, $route);

        return $route;
    }

    private function addBindings(Application $app, array &$additionalMethodParams): void
    {
        if (!isset($this->bindingsClasses)) {
            $this->bindingsClasses = ClassFinder::getClassesInNamespace('User\Swoole\Domain\Entities');
        }

        foreach ($additionalMethodParams as $key => $value) {
            $found = null;

            foreach ($this->bindingsClasses as $class) {
                if (str_ends_with($class, '\\' . ucfirst($key))) {
                    $found = $class;
                    break;
                }
            }

            if($found){
                /** @var EntityManager $em */
                $em = $app->make(EntityManager::class);
                $entity = $em->getRepository($class)->find($value);

                if(!$entity){
                    throw new HttpException('Resource not found', 404);
                }

                $additionalMethodParams[$key] = $entity;
            }
        }
    }
}
