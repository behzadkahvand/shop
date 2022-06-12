<?php

namespace App\Service\Order\Condition;

use App\Entity\Order;

interface OrderConditionInterface
{
    public function apply(Order $order): void;
}
