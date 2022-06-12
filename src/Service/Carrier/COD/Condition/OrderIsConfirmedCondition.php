<?php

namespace App\Service\Carrier\COD\Condition;

use App\Dictionary\OrderStatus;
use App\Entity\OrderShipment;
use App\Service\Carrier\Exceptions\OrderIsNotConfirmedException;

class OrderIsConfirmedCondition implements CODConditionInterface
{
    public function apply(OrderShipment $orderShipment): void
    {
        if ($orderShipment->getOrder()->getStatus() !== OrderStatus::CONFIRMED) {
            throw new OrderIsNotConfirmedException();
        }
    }
}
