<?php
declare(strict_types=1);

namespace User\Swoole\Infrastructure\Http\Request\Traits;

use Doctrine\ORM\QueryBuilder;
use Exception;
use User\Swoole\Infrastructure\Http\Request\Request;

trait UsesRequestParams
{
    private function applyAll(Request $request, QueryBuilder $query): int
    {
        $this->applyFilters($request, $query);
        $this->applySearch($request, $query);

        return $this->applyPagination($request, $query);
    }

    private function applyFilters(Request $request, QueryBuilder $query): void
    {
        $filters = $request->query('filter', []);

        $alias = $query->getRootAliases()[0];

        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $operator = 'IN';
            } elseif (str_contains($key, ':')) {
                [$key, $operatorText] = explode(':', $key);
                $operator = $this->operatorFromText($operatorText);
            } else {
                $operator = '=';
            }

            $query
                ->andWhere("$alias.$key $operator (:$key)")
                ->setParameter($key, $value);
        }
    }

    private function applySearch(Request $request, QueryBuilder $query): void
    {
        $search = $request->query('search');

        if ($search) {
            $alias = $query->getRootAliases()[0];

            $query
                ->andWhere("$alias.name LIKE :search")
                ->setParameter('search', "%$search%");
        }
    }

    private function applyPagination(Request $request, QueryBuilder $query, bool $count = true): int
    {
        $total = $count
            ? (clone($query))->select('COUNT(u.id)')->getQuery()->getSingleScalarResult()
            : 0;

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('perPage', 20);

        $query
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return $total;
    }

    private function operatorFromText(string $operatorText)
    {
        return match ($operatorText) {
            'eq' => '=',
            'neq' => '!=',
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'like' => 'LIKE',
            'nlike' => 'NOT LIKE',
            default => throw new Exception('Invalid operator'),
        };
    }
}