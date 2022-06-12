<?php

namespace App\Service\ORM\Filter;

use App\Entity\Inventory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class InventoryHasStockFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (Inventory::class !== $targetEntity->getName()) {
            return '';
        }

        return sprintf('%1$s.seller_stock > 0', $targetTableAlias);
    }
}
