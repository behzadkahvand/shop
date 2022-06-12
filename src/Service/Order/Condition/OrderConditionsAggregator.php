<?php

namespace App\Service\Order\Condition;

use App\Entity\Order;
use Throwable;

class OrderConditionsAggregator implements OrderConditionInterface
{
    protected iterable $conditions;

    public function __construct(iterable $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @throws Throwable
     */
    public function apply(Order $order): void
    {
        foreach ($this->conditions as $condition) {
            $condition->apply($order);
        }
    }
}
