<?php

namespace App\Service\Product\Search\Utils\Doctrine;

use Doctrine\ORM\QueryBuilder;

interface InventoryPriceRangeInterface
{
    public function getPriceRange(QueryBuilder $queryBuilder, array $filters): array;
}
