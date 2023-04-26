<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Transformers;

use User\Swoole\Domain\Entities\User;
use User\Swoole\Infrastructure\View\TransformerInterface;

class UserTransformer implements TransformerInterface
{
    /**
     * @param User $data
     * @return array
     */
    public function transform($data): array
    {
        return [
            'id' => $data->getId(),
            'email' => $data->getEmail(),
            'password' => $data->getPassword(),
        ];
    }
}