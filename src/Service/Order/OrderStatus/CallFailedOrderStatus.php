<?php

namespace App\Service\Order\OrderStatus;

use App\Dictionary\OrderStatus;
use App\Entity\Order;

class CallFailedOrderStatus extends AbstractOrderStatus
{
    public function waitCustomer(Order $order): void
    {
        $order->setStatus(OrderStatus::WAIT_CUSTOMER);
    }

    public function support(string $status): bool
    {
        return OrderStatus::CALL_FAILED === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::CANCELED,
            OrderStatus::REFUND,
        ];
    }
}
