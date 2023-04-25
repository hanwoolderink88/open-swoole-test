<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Controllers;

use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\Response;

class UserController
{
    public function index(): Response
    {
        return new Response(200, [], 'Users');
    }

    public function show(Request $request, string $userId): Response
    {
        return new Response(200, [], 'User with id: ' . $userId);
    }
}
