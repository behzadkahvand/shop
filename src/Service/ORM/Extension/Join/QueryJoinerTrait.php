<?php

namespace App\Service\ORM\Extension\Join;

use App\Service\ORM\QueryContext;
use Doctrine\ORM\QueryBuilder;

/**
 * Trait QueryJoinerTrait
 */
trait QueryJoinerTrait
{
    private QueryJoiner $queryJoiner;

    /**
     * @param string $field
     *
     * @return array
     */
    private function extractFieldFromRelations(string $field): array
    {
        $parts        = explode('.', $field);
        $field        = array_pop($parts);
        $associations = $parts;

        return [$associations, $field];
    }

    /**
     * @param array $relations
     * @param string $resourceClass
     * @param QueryContext $context
     * @param QueryBuilder $queryBuilder
     * @param int $joinType
     *
     * @return string
     */
    private function applyJoins(string $resourceClass, array $relations, QueryContext $context, QueryBuilder $queryBuilder, int $joinType): string
    {
        $rootAlias = $context->getRootAlias();

        foreach ($relations as $relation) {
            $relationClass = $this->getRelationClass($resourceClass, $relation);

            if ($context->hasAlias($resourceClass, $relationClass)) {
                $rootAlias     = $context->getAlias($resourceClass, $relationClass);
                $resourceClass = $relationClass;

                $context->changeCurrentAlias($rootAlias);

                continue;
            }

            $data = new QueryJoinerData($rootAlias, $relation, $resourceClass, $relationClass);

            [$resourceClass, $rootAlias] = $this->join($queryBuilder, $context, $data, $joinType);
        }

        return $resourceClass;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param QueryContext $context
     * @param QueryJoinerData $data
     * @param int $joinType
     *
     * @return array
     */
    private function join(QueryBuilder $queryBuilder, QueryContext $context, QueryJoinerData $data, int $joinType): array
    {
        return $this->queryJoiner->join($queryBuilder, $context, $data, $joinType);
    }
}
