<?php

namespace App\Service\Carrier\COD\Condition;

use App\Entity\OrderShipment;

interface CODConditionInterface
{
    public function apply(OrderShipment $orderShipment): void;
}
