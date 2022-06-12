<?php

namespace App\Service\Carrier\COD\Condition;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;
use App\Service\Carrier\Exceptions\ShipmentStatusIsNotSentException;

class ShipmentStatusIsSentCondition implements CODConditionInterface
{
    public function apply(OrderShipment $orderShipment): void
    {
        if ($orderShipment->getStatus() !== OrderShipmentStatus::SENT) {
            throw new ShipmentStatusIsNotSentException();
        }
    }
}
