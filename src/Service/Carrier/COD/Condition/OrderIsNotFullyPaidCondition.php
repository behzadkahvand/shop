<?php

namespace App\Service\Carrier\COD\Condition;

use App\Entity\OrderShipment;
use App\Service\Carrier\Exceptions\OrderAlreadyHasBeenFullyPaidException;

class OrderIsNotFullyPaidCondition implements CODConditionInterface
{
    public function apply(OrderShipment $orderShipment): void
    {
        if ($orderShipment->getOrder()->getPaidAt() !== null) {
            throw new OrderAlreadyHasBeenFullyPaidException();
        }
    }
}
