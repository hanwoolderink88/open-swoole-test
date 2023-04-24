<?php

namespace User\Swoole\Domain\Controllers;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;

class UserController
{
    public function index(): Response
    {
        return new Response(200, [], 'Users');
    }

    public function show(Request $request, string $userId): Response
    {
        $params = [];
        parse_str($request->getUri()->getQuery(), $params);

        return new Response(200, [], 'User with id: ' . $userId);
    }
}
