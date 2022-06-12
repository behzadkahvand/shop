<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\OrderShipmentStatusLog\ValueObjects\CreateOrderShipmentStatusLogValueObject;

class OrderShipmentStatusFactory
{
    private iterable $orderShipmentStatuses;

    public function __construct(iterable $orderShipmentStatuses)
    {
        $this->orderShipmentStatuses = $orderShipmentStatuses;
    }

    public function create(string $status): AbstractOrderShipmentStatus
    {
        foreach ($this->orderShipmentStatuses as $orderShipmentStatus) {
            if ($orderShipmentStatus->support($status)) {
                return $orderShipmentStatus;
            }
        }

        throw new InvalidOrderShipmentStatusTransitionException();
    }
}
