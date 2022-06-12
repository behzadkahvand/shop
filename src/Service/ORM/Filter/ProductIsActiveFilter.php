<?php

namespace App\Service\ORM\Filter;

use App\Entity\Product;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class ProductIsActiveFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->getName() !== Product::class) {
            return "";
        }

        return $targetTableAlias . '.is_active = 1';
    }
}
