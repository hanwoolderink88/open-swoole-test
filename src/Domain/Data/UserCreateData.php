<?php
declare(strict_types=1);

namespace User\Swoole\Domain\Data;

use Hanwoolderink\Data\Attributes\Rule;
use Hanwoolderink\Data\Dto\Data;

class UserCreateData extends Data
{
    #[Rule('required|email|min:5|max:255')]
    public string $email;

    #[Rule('required|string|min:5|max:255')]
    public string $password;
}