<?php

namespace App\Service\Carrier\COD\Condition;

use App\Entity\OrderShipment;

class CODConditionsAggregator
{
    protected iterable $conditions;

    public function __construct(iterable $conditions)
    {
        $this->conditions = $conditions;
    }

    public function apply(OrderShipment $orderShipment): void
    {
        /** @var CODConditionInterface $condition */
        foreach ($this->conditions as $condition) {
            $condition->apply($orderShipment);
        }
    }
}
