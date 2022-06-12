<?php

namespace App\Service\ORM\Filter;

use App\Entity\Inventory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class InventoryIsActiveFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->getName() !== Inventory::class) {
            return "";
        }

        return $targetTableAlias . '.is_active = 1';
    }
}
