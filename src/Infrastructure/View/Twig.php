<?php

namespace User\Swoole\Infrastructure\View;

use Nyholm\Psr7\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Twig
{
    // todo: move
    public static function response(string $template, array $data): Response
    {
        $twig = new self();

        $template = strpos($template, '.twig') === false ? $template . '.twig' : $template;
        $body = $twig->render($template, $data);

        $response = new Response(200, [], $body);
        $response->withHeader('Content-Type', 'text/html');

        return $response;
    }

    public function render(string $template, array $data): string
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../../resources/views');

        $twig = new Environment($loader, [
            'cache' => __DIR__ . '/../../../cache',
            'debug' => true,
        ]);

        return $twig->render($template, $data);
    }
}
