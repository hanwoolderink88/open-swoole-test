<?php

namespace User\Swoole\Infrastructure\Auth\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    protected $code = 401;
}