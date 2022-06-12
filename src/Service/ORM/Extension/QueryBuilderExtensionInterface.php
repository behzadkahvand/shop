<?php

namespace App\Service\ORM\Extension;

use App\Service\ORM\QueryContext;
use Doctrine\ORM\QueryBuilder;

/**
 * Interface QueryBuilderExtensionInterface.
 */
interface QueryBuilderExtensionInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $resourceClass
     * @param QueryContext $context
     */
    public function applyToCollection(QueryBuilder $queryBuilder, string $resourceClass, QueryContext $context);
}
