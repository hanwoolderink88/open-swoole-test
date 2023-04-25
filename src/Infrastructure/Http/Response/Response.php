<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Http\Response;

use Nyholm\Psr7\Response as PsrResponse;

/**
 * @mixin PsrResponse
 */
class Response
{
    private PsrResponse $response;

    public function __construct(
        int $status = 200,
        array $headers = [],
        $body = null,
        string $version = '1.1',
        string $reason = null,
    ) {
        $this->response = new PsrResponse($status, $headers, $body, $version, $reason);
    }

    public function __call($name, $arguments)
    {
        return $this->response->{$name}(...$arguments);
    }
}