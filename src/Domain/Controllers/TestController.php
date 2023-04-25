<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Controllers;

use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\Response;
use User\Swoole\Infrastructure\View\Twig;

class TestController
{
    public function home(Request $request): Response
    {
        return Twig::response('pages/home', [
            'path' => $request->getUri()->getPath(),
            'title' => 'Homepage',
            'image' => '/img/logo.png',
            'user' => $request->getUser(),
        ]);
    }

    public function about(Request $request): Response
    {
        return new Response(200, [], 'about: ' .  $request->getUri());
    }
}
