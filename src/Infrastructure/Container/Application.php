<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Container;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;

class Application extends Container
{
    private string $basePath;

    /**
     * @template T
     * @param class-string<T> $abstract
     * @param array $parameters
     * @return T
     * @throws BindingResolutionException
     */
    public function make($abstract, array $parameters = [])
    {
        return parent::make($abstract, $parameters);
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;


    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
