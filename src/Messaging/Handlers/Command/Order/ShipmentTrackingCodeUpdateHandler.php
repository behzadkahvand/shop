<?php

namespace App\Messaging\Handlers\Command\Order;

use App\Messaging\Messages\Command\Order\ShipmentTrackingCodeUpdate;
use App\Service\OrderShipment\ShipmentTrackingCodeUpdateService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ShipmentTrackingCodeUpdateHandler implements MessageHandlerInterface
{
    public function __construct(private ShipmentTrackingCodeUpdateService $trackingCodeUpdateService)
    {
    }

    public function __invoke(ShipmentTrackingCodeUpdate $orderTrackingCodeUpdate): void
    {
        $this->trackingCodeUpdateService->processBatchUpdateTrackingCodes($orderTrackingCodeUpdate->getTrackingCodeId());
    }
}
