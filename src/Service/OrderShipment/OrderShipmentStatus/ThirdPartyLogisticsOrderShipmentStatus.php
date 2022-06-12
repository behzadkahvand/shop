<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;

class ThirdPartyLogisticsOrderShipmentStatus extends AbstractOrderShipmentStatus
{
    public function support(string $status): bool
    {
        return OrderShipmentStatus::THIRD_PARTY_LOGISTICS === $status;
    }

    public function validTransitions(): array
    {
        return [
            OrderShipmentStatus::CANCELED,
        ];
    }
}
