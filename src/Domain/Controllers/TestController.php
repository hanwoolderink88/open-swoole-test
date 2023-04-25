<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Controllers;

use User\Swoole\Application\Middleware\AuthMiddleware;
use User\Swoole\Application\Middleware\TestMiddleware;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\Response;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Middleware;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Route;
use User\Swoole\Infrastructure\View\Twig;

class TestController
{
    #[Route('/')]
    #[MiddleWare(AuthMiddleware::class)]
    public function home(Request $request): Response
    {
        return Twig::response('pages/home', [
            'title' => 'Homepage',
            'image' => '/img/logo.png',
            'user' => $request->getUser()->email,
        ]);
    }

    #[Route('/about')]
    #[Middleware(TestMiddleware::class)]
    #[MiddleWare(AuthMiddleware::class)]
    public function about(Request $request): Response
    {
        return Twig::response('pages/about', [
            'title' => 'About',
        ]);
    }
}
