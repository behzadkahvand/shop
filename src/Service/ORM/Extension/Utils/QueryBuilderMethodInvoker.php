<?php

namespace App\Service\ORM\Extension\Utils;

use App\Service\ORM\Extension\QueryAliasNameGenerator;
use Doctrine\ORM\QueryBuilder;

/**
 * Class QueryBuilderMethodInvoker
 */
class QueryBuilderMethodInvoker
{
    public function invoke(QueryBuilder $queryBuilder, string $method, string $alias, string $operator, $value)
    {
        if ('BETWEEN' === $operator) {
            $this->applyBetweenClause($alias, $queryBuilder, $value);
        } elseif (in_array($method, ['isNull', 'isNotNull'])) {
            $this->applyIsNullClause($queryBuilder, $alias, 'isNotNull' === $method);
        } else {
            if ('like' === $method) {
                $value = '%' . trim($value, '%') . '%';
            }

            $parameterName = QueryAliasNameGenerator::generate($alias);

            $queryBuilder->andWhere($queryBuilder->expr()->{$method}($alias, ":{$parameterName}"))
                         ->setParameter($parameterName, $value);
        }
    }

    /**
     * @param string       $alias
     * @param QueryBuilder $queryBuilder
     * @param              $value
     */
    private function applyBetweenClause(string $alias, QueryBuilder $queryBuilder, $value): void
    {
        $firstParameter  = QueryAliasNameGenerator::generate($alias);
        $secondParameter = $firstParameter . '_2';
        $expr            = $queryBuilder->expr()->between($alias, ":$firstParameter", ":$secondParameter");

        $queryBuilder->andWhere($expr)
                     ->setParameter($firstParameter, $value[0])
                     ->setParameter($secondParameter, $value[1]);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $alias
     */
    private function applyIsNullClause(QueryBuilder $queryBuilder, string $alias, bool $negate = false): void
    {
        $queryBuilder->andWhere($queryBuilder->expr()->{$negate ? 'isNotNull' : 'isNull'}($alias));
    }
}
