<?php
declare(strict_types=1);

namespace User\Swoole\Application\Middleware;

use User\Swoole\Infrastructure\Auth\Models\User;
use User\Swoole\Infrastructure\Http\Middleware\MiddlewareInterface;
use User\Swoole\Infrastructure\Http\Request\Request;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        $auth = $request->getHeader('Authorization');

        if ($auth === []) {
            // throw new UnauthorizedException('No token specified');
        }

        $user = new User();

        $user->email = 'admin@test.nl';
        $user->password = 'SuperSecretPassword';

        $request->setUser($user);
    }
}