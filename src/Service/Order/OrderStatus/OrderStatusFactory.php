<?php

namespace App\Service\Order\OrderStatus;

use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;

class OrderStatusFactory
{
    private iterable $orderStatuses;

    public function __construct(iterable $orderStatuses)
    {
        $this->orderStatuses = $orderStatuses;
    }

    public function create(string $status): AbstractOrderStatus
    {
        foreach ($this->orderStatuses as $orderStatus) {
            if ($orderStatus->support($status)) {
                return $orderStatus;
            }
        }

        throw new InvalidOrderStatusTransitionException();
    }

    public function getCreateOrderStatusLogValueObject(): CreateOrderStatusLogValueObject
    {
        return new CreateOrderStatusLogValueObject();
    }
}
