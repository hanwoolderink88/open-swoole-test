<?php

namespace User\Swoole\Infrastructure\Http\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware
{
    public function __construct(public string $middleware)
    {
    }
}