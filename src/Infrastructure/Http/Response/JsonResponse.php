<?php

namespace User\Swoole\Infrastructure\Http\Response;

use Nyholm\Psr7\Response as PsrResponse;

/**
 * @mixin PsrResponse
 */
class JsonResponse implements ResponseInterface
{
    private PsrResponse $response;

    public function __construct(
        array $body = [],
        int $status = 200,
        array $headers = [],
        string $version = '1.1',
        string $reason = null,
    ) {
        $headers['Content-Type'] = ['application/json'];
        $body = is_string($body) ? $body : json_encode($body);

        $this->response = new PsrResponse($status, $headers, $body, $version, $reason);
    }

    public function __call($name, $arguments)
    {
        return $this->response->{$name}(...$arguments);
    }
}