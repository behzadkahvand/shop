<?php

namespace App\Service\ORM\Filter;

use App\Entity\OrderShipment;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class ShipmentHasOrderItemFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->getName() !== OrderShipment::class) {
            return "";
        }

        return "EXISTS (SELECT id FROM order_items WHERE order_shipment_id = $targetTableAlias.id)";
    }
}
