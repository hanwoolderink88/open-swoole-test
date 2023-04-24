<?php

namespace User\Swoole\Infrastructure\Http\Request;

use Nyholm\Psr7\Request as PsrRequest;
use User\Swoole\Infrastructure\Auth\Models\User;
use function JmesPath\search;

/**
 * @mixin PsrRequest
 */
class Request
{
    private PsrRequest $request;

    private User $user;

    public function __construct(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        string $version = '1.1',
    ) {
        $this->request = new PsrRequest($method, $uri, $headers, $body, $version);
    }

    public static function createFromPsrRequest(PsrRequest $psrRequest): static
    {
        return new static(
            $psrRequest->getMethod(),
            $psrRequest->getUri(),
            $psrRequest->getHeaders(),
            $psrRequest->getBody(),
            $psrRequest->getProtocolVersion(),
        );
    }

    public function __call($name, $arguments)
    {
        return $this->request->{$name}(...$arguments);
    }

    public function query($key = null, $default = null): mixed
    {
        if ($key === null) {
            return $this->request->getUri()->getQuery();
        }

        $params = [];

        parse_str($this->request->getUri()->getQuery(), $params);

        $found = search($key, $params);

        return $found ?? $default;
    }

    public function get(string $key, $default = null): mixed
    {
        $params = $this->request->getBody()->getContents();

        if ($this->request->getHeader('Content-Type') === 'application/json') {
            $body = json_decode($params, true);
        } else {
            $body = [];
            parse_str($params, $body);
        }

        $found = search($key, $body);

        return $found ?? $default;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}