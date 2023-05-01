<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Modules;

use Doctrine\ORM\EntityManager;
use Hanwoolderink\Data\Dto\Data;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\RouteCollection;
use User\Swoole\Application\Modules\BaseModule;
use User\Swoole\Infrastructure\Container\Application;
use User\Swoole\Infrastructure\Http\Exceptions\HttpException;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\JsonResponse;
use User\Swoole\Infrastructure\Http\Response\Response;
use User\Swoole\Infrastructure\Http\Response\ResponseInterface;
use User\Swoole\Infrastructure\Http\Routing\Router;
use User\Swoole\Infrastructure\Http\Routing\RouterFactory;
use User\Swoole\Infrastructure\Persistence\EntityManagerFactory;

class Kernel
{
    private Application $app;

    private array $modules = [
        BaseModule::class,
    ];

    public function __construct(private string $basePath)
    {
    }

    public function handle(Request $request): ResponseInterface
    {
        $this->init($request);

        return $this->getResponse();
    }

    public function getApp(): Application
    {
        return $this->app;
    }

    private function init(Request $request): void
    {
        $app = Application::getInstance();
        $app->setBasePath($this->basePath);

        $this->app = $app;

        $this->initBase($request);

        foreach ($this->modules as $module) {
            if (is_string($module) && is_a($module, ModuleInterface::class, true)) {
                $this->initModule($module);
            }
        }
    }

    private function initBase(Request $request): void
    {
        $this->app->singletonIf(Router::class, function (Application $app) {
            $router = new Router();
            $router->setRoutes(new RouteCollection());

            return $router;
        });

        $this->app->singletonIf(EntityManager::class, function (Application $app) {
            return EntityManagerFactory::create();
        });

        $this->app->singleton(Request::class, function (Application $app) use ($request) {
            return $request;
        });

        $this->app->afterResolving(Data::class, function (Data $abstract, Application $app) {
            /** @var Request $request */
            $request = $app->make(Request::class);

            return $abstract->setData($request->get())->validate();
        });
    }

    private function initModule(string $module): void
    {
        /** @var ModuleInterface $module */
        $module = new $module($this->app);

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $module->routes($router);
        $module->container();
    }

    private function getResponse(): ResponseInterface
    {
        $app = $this->getApp();
        $router = $app->make(Router::class);

        try {
            $appResponse = $router->route($app);
        } catch (HttpException $e) {
            $appResponse = new Response(
                $e->getCode(),
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ])
            );
        } catch (ValidationException $e) {
            $appResponse = new Response(
                422,
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode([
                    'code' => 422,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ])
            );
        }

        return $appResponse;
    }
}