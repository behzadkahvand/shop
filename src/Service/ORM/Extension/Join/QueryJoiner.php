<?php

namespace App\Service\ORM\Extension\Join;

use App\Service\ORM\Extension\QueryAliasNameGenerator;
use App\Service\ORM\QueryContext;
use Doctrine\ORM\QueryBuilder;

/**
 * Class QueryJoiner
 */
class QueryJoiner
{
    public const JOIN_TYPE_INNER = 1;
    public const JOIN_TYPE_LEFT = 2;

    /**
     * @param QueryBuilder $queryBuilder
     * @param QueryContext $context
     * @param QueryJoinerData $data
     * @param int $joinType
     *
     * @return array
     */
    public function join(QueryBuilder $queryBuilder, QueryContext $context, QueryJoinerData $data, int $joinType): array
    {
        if (!in_array($joinType, [self::JOIN_TYPE_INNER, self::JOIN_TYPE_LEFT])) {
            throw new \InvalidArgumentException('Invalid join type given.');
        }

        $entityAlias   = $data->getEntityAlias();
        $relationField = $data->getRelationField();
        $entityClass   = $data->getEntityClass();
        $relationClass = $data->getRelationClass();
        $join          = sprintf('%s.%s', $entityAlias, $relationField);
        $method        = self::JOIN_TYPE_INNER === $joinType ? 'innerJoin' : 'leftJoin';
        $joinAlias     = QueryAliasNameGenerator::generate($join);

        $context->setAlias($entityClass, $relationClass, $joinAlias);
        $context->changeCurrentAlias($joinAlias);

        $queryBuilder->$method($join, $joinAlias);

        if (self::JOIN_TYPE_INNER === $joinType) {
            $queryBuilder->addSelect($joinAlias);
        }

        return [$relationClass, $joinAlias];
    }
}
