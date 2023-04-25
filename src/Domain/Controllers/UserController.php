<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Controllers;

use User\Swoole\Application\Middleware\AuthMiddleware;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\JsonResponse;
use User\Swoole\Infrastructure\Http\Response\Response;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Middleware;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Route;

class UserController
{
    #[Route('/user')]
    #[Middleware(AuthMiddleware::class)]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            [
                'id' => 1,
                'name' => 'John Doe',
            ],
            [
                'id' => 2,
                'name' => 'Jane Doe',
            ],
        ]);
    }

    #[Route('/user/{userId}')]
    #[Middleware(AuthMiddleware::class)]
    public function show(Request $request, string $userId): Response
    {
        return new Response(200, [
            'Content-Type' => 'application/json',
        ], json_encode([
            'id' => $userId,
            'name' => 'John Doe',
        ]));
    }
}
