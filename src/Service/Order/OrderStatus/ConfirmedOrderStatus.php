<?php

namespace App\Service\Order\OrderStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Order;

class ConfirmedOrderStatus extends AbstractOrderStatus
{
    public function support(string $status): bool
    {
        return OrderStatus::CONFIRMED === $status;
    }

    public function delivered(Order $order): void
    {
        $order->setStatus(OrderStatus::DELIVERED);

        $this->changeShipmentStatuses($order, OrderShipmentStatus::DELIVERED);
    }

    public function validTransitions(): array
    {
        return [
            OrderStatus::DELIVERED,
            OrderStatus::CANCELED,
            OrderStatus::REFUND,
        ];
    }
}
