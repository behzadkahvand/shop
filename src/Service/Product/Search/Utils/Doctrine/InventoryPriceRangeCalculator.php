<?php

namespace App\Service\Product\Search\Utils\Doctrine;

use Doctrine\ORM\QueryBuilder;

class InventoryPriceRangeCalculator implements InventoryPriceRangeInterface
{
    public function getPriceRange(QueryBuilder $queryBuilder, array $filters): array
    {
        [$rootAlias] = $queryBuilder->getRootAliases();

        [$range] = $queryBuilder->innerJoin("{$rootAlias}.buyBox", "buyBox")
                                ->select('COALESCE(MIN(buyBox.finalPrice), 0) as min, COALESCE(MAX(buyBox.finalPrice), 0) as max')
                                ->getQuery()
                                ->getResult();

        return array_map('intval', $range);
    }
}
