<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\View;

interface TransformerInterface
{
    public function transform($data): array;
}