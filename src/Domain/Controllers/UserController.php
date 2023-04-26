<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Controllers;

use Doctrine\ORM\EntityManager;
use User\Swoole\Application\Middleware\AuthMiddleware;
use User\Swoole\Domain\Entities\User;
use User\Swoole\Domain\Transformers\UserTransformer;
use User\Swoole\Infrastructure\Http\ControllerResponse\JsonControllerResponse;
use User\Swoole\Infrastructure\Http\Exceptions\HttpException;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Response\JsonResponse;
use User\Swoole\Infrastructure\Http\Response\Response;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Middleware;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Route;

#[Middleware(AuthMiddleware::class)]
class UserController
{
    public function __construct(
        private Request $request,
        private EntityManager $em,
        private JsonControllerResponse $response,
    ) {
    }

    #[Route('/user', 'GET')]
    public function index(): JsonResponse
    {
        $users = $this->em->getRepository(User::class);

        return $this->response->index($users->findAll(), new UserTransformer);
    }

    #[Route('/user/{userId}', 'GET')]
    public function show(string $userId): JsonResponse|Response
    {
        $user = $this->em->getRepository(User::class)->find($userId);

        if (!$user) {
            throw new HttpException('User not found', 404);
        }

        return $this->response->show($user, new UserTransformer);
    }

    #[Route('/user', 'POST')]
    public function store(): Response
    {
        $user = new User();
        $user->setEmail($this->request->get('email'));
        $user->setPassword($this->request->get('password'));

        $this->em->persist($user);
        $this->em->flush();

        return new Response(201);
    }
}
