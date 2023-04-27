<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Data;

use Hanwoolderink\Data\Attributes\Rule;
use Hanwoolderink\Data\Dto\Data;

class UserUpdateData extends Data
{
    #[Rule('string|min:5|max:255')]
    public string $password;
}