<?php

namespace App\Service\Utils\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatorUtils
{
    public function getCount(QueryBuilder $queryBuilder): int
    {
        $builder = clone $queryBuilder;

        [$rootAlias] = $builder->getRootAliases();

        $builder->select('partial ' . $rootAlias . '.{id}');

        return (new Paginator($builder->distinct(true)))->count();
    }

    public function getPaginator($query)
    {
        return new Paginator($query);
    }
}
