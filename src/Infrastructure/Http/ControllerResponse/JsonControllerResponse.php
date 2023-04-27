<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Http\ControllerResponse;

use User\Swoole\Infrastructure\Http\Response\JsonResponse;
use User\Swoole\Infrastructure\View\TransformerInterface;

class JsonControllerResponse
{
    public function index(
        array $data,
        TransformerInterface $transformer,
        int $page,
        int $perPage,
        int $total,
    ): JsonResponse {
        return new JsonResponse([
            'meta' => [
                'items' => count($data),
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => (int) ceil($total / $perPage),
            ],
            'data' => array_map(fn(mixed $item) => $transformer->transform($item), $data),
        ]);
    }

    public function show(mixed $data, TransformerInterface $transformer, int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'meta' => [],
            'data' => $transformer->transform($data),
        ]);
    }
}