<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Repositories;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function paginate(int $page = 1, int $perPage = 10, array $params = [],  ): array
    {
        $offset = ($page - 1) * $perPage;

        $query = $this->createQueryBuilder('u')
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->getQuery();

        return $query->getResult();
    }
}