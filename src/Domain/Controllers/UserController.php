<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Controllers;

use Doctrine\ORM\EntityManager;
use User\Swoole\Application\Middleware\AuthMiddleware;
use User\Swoole\Domain\Actions\User\CreateUser;
use User\Swoole\Domain\Data\UserCreateData;
use User\Swoole\Domain\Entities\User;
use User\Swoole\Domain\Transformers\UserTransformer;
use User\Swoole\Infrastructure\Http\ControllerResponse\JsonControllerResponse;
use User\Swoole\Infrastructure\Http\Request\Request;
use User\Swoole\Infrastructure\Http\Request\Traits\UsesRequestParams;
use User\Swoole\Infrastructure\Http\Response\JsonResponse;
use User\Swoole\Infrastructure\Http\Response\Response;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Middleware;
use User\Swoole\Infrastructure\Http\Routing\Attributes\Route;

#[Middleware(AuthMiddleware::class)]
class UserController
{
    use UsesRequestParams;

    public function __construct(
        private Request $request,
        private JsonControllerResponse $response,
        private UserTransformer $transformer,
    ) {
    }

    #[Route('/user', 'GET')]
    public function index(EntityManager $em): JsonResponse
    {
        $query = $em->getRepository(User::class)->createQueryBuilder('u');
        $total = $this->applyAll($this->request, $query);

        return $this->response->index(
            $query->getQuery()->getResult(),
            $this->transformer,
            $this->request->getPage(),
            $this->request->getPerPage(),
            $total,
        );
    }

    #[Route('/user/{user}', 'GET')]
    public function show(User $user): JsonResponse
    {
        return $this->response->show($user, new UserTransformer);
    }

    #[Route('/user', 'POST')]
    public function store(CreateUser $action, UserCreateData $data): JsonResponse
    {
        return $this->response->show($action->handle($data), $this->transformer, 201);
    }

    #[Route('/user/{user}', 'PUT')]
    public function update(User $user, UserCreateData $data, EntityManager $em): JsonResponse
    {
        if ($data->password) {
            $user->setPassword(password_hash($data->password, PASSWORD_BCRYPT));
        }

        $em->persist($user);
        $em->flush();

        return $this->response->show($user, new UserTransformer, 202);
    }

    #[Route('/user/{user}', 'DELETE')]
    public function destroy(User $user, EntityManager $em): Response
    {
        $em->remove($user);
        $em->flush();

        return new Response(204);
    }
}
