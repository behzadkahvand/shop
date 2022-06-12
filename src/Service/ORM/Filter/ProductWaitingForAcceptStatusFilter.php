<?php

namespace App\Service\ORM\Filter;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Product;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Class ProductWaitingForAcceptStatusFilter
 */
final class ProductWaitingForAcceptStatusFilter extends SQLFilter
{
    /**
     * @inheritDoc
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->getName() !== Product::class) {
            return "";
        }

        return sprintf('%s.status <> "%s"', $targetTableAlias, ProductStatusDictionary::WAITING_FOR_ACCEPT);
    }
}
