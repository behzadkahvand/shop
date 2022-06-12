<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;

class CanceledByCustomerOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function support(string $status): bool
    {
        return OrderShipmentStatus::CANCELED_BY_CUSTOMER === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::CANCELED,
        ];
    }
}
