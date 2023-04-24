<?php

namespace User\Swoole\Domain\Controllers;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use User\Swoole\Infrastructure\View\Twig;

class TestController
{
    public function home(Request $request): Response
    {
        return Twig::response('pages/home', [
            'path' => $request->getUri()->getPath(),
            'title' => 'Homepage',
            'image' => '/img/logo.png',
        ]);
    }

    public function about(Request $request): Response
    {
        return new Response(200, [], 'about: ' .  $request->getUri());
    }
}
