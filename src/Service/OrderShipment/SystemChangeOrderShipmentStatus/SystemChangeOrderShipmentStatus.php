<?php

namespace App\Service\OrderShipment\SystemChangeOrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\Order;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\OrderShipment\SystemChangeOrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusException;

class SystemChangeOrderShipmentStatus
{
    private OrderShipmentStatusService $orderShipmentStatusService;

    public function __construct(OrderShipmentStatusService $orderShipmentStatusService)
    {
        $this->orderShipmentStatusService = $orderShipmentStatusService;
    }

    public function change(Order $order, string $nextOrderShipmentStatus): void
    {
        if (!OrderShipmentStatus::isValid($nextOrderShipmentStatus)) {
            throw new InvalidOrderShipmentStatusException();
        }

        foreach ($order->getShipments() as $orderShipment) {
            if (in_array($orderShipment->getStatus(), [$nextOrderShipmentStatus, OrderShipmentStatus::CANCELED])) {
                continue;
            }

            $this->orderShipmentStatusService->change($orderShipment, $nextOrderShipmentStatus);
        }
    }
}
