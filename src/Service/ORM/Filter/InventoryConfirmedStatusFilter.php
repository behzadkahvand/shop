<?php

namespace App\Service\ORM\Filter;

use App\Dictionary\InventoryStatus;
use App\Entity\Inventory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class InventoryConfirmedStatusFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->getName() !== Inventory::class) {
            return "";
        }

        return $targetTableAlias . '.status = "' . InventoryStatus::CONFIRMED . '"';
    }
}
