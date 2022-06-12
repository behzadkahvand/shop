<?php

namespace App\Service\ORM\Filter;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Product;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Class ProductTrashedStatusFilter
 */
final class ProductTrashedStatusFilter extends SQLFilter
{
    /**
     * @inheritDoc
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (Product::class !== $targetEntity->getName()) {
            return '';
        }

        return sprintf('%s.status <> "%s"', $targetTableAlias, ProductStatusDictionary::TRASHED);
    }
}
