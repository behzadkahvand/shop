<?php

namespace App\Messaging\Messages\Command\Order;

final class ShipmentTrackingCodeUpdate
{
    public function __construct(private int $trackingCodeId)
    {
    }

    public function getTrackingCodeId(): int
    {
        return $this->trackingCodeId;
    }
}
