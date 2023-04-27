<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Actions\User;

use Doctrine\ORM\EntityManager;
use User\Swoole\Domain\Data\UserCreateData;
use User\Swoole\Domain\Entities\User;

class CreateUser
{
    public function __construct(private EntityManager $em)
    {
    }

    public function handle(UserCreateData $data): User
    {
        $user = new User();
        $user->setEmail($data->email);
        $user->setPassword(password_hash($data->password, PASSWORD_BCRYPT));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}