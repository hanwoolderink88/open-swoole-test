<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Http\ControllerResponse;

use User\Swoole\Infrastructure\Http\Response\JsonResponse;
use User\Swoole\Infrastructure\View\TransformerInterface;

class JsonControllerResponse
{
    public function index(array $data, TransformerInterface $transformer): JsonResponse
    {
        return new JsonResponse([
            'meta' => [
                'count' => count($data),
            ],
            'data' => array_map(fn(mixed $item) => $transformer->transform($item), $data),
        ]);
    }

    public function show(mixed $data, TransformerInterface $transformer): JsonResponse
    {
        return new JsonResponse([
            'meta' => [],
            'data' => $transformer->transform($data),
        ]);
    }
}