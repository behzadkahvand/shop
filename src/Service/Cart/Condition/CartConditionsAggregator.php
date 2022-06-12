<?php

namespace App\Service\Cart\Condition;

use App\Entity\Inventory;
use Throwable;

class CartConditionsAggregator implements CartConditionInterface
{
    protected iterable $conditions;

    public function __construct(iterable $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @throws Throwable
     */
    public function apply(Inventory $inventory, int $quantity): void
    {
        /** @var CartConditionInterface $condition */
        foreach ($this->conditions as $condition) {
            $condition->apply($inventory, $quantity);
        }
    }
}
