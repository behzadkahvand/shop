<?php

namespace App\Service\Order\OrderStatus;

use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;

class RefundOrderStatus extends AbstractOrderStatus
{
    public function refund(Order $order): void
    {
        throw new InvalidOrderStatusTransitionException();
    }

    public function support(string $status): bool
    {
        return OrderStatus::REFUND === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderStatus::CANCELED,
        ];
    }
}
