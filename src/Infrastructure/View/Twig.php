<?php

namespace User\Swoole\Infrastructure\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use User\Swoole\Infrastructure\Container\Application;
use User\Swoole\Infrastructure\Http\Response\Response;

class Twig
{
    public static function response(string $template, array $data): Response
    {
        $twig = new self();

        $template = str_contains($template, '.twig') ? $template : $template . '.twig';
        $body = $twig->render($template, $data);

        $response = new Response(200, [], $body);
        $response->withHeader('Content-Type', 'text/html');

        return $response;
    }

    public function render(string $template, array $data): string
    {
        $basePath = Application::getInstance()->getBasePath();

        $loader = new FilesystemLoader($basePath . '/resources/views');

        $twig = new Environment($loader, [
            'cache' => $basePath . '/storage/cache',
            'debug' => true,
        ]);

        return $twig->render($template, $data);
    }
}
