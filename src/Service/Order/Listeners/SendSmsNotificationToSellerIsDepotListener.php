<?php

namespace App\Service\Order\Listeners;

use App\Entity\Order;
use App\Events\Order\OrderRegisteredEvent;
use App\Service\Inventory\DepotInventory\DepotStatusInventoryService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendSmsNotificationToSellerIsDepotListener implements EventSubscriberInterface
{
    private DepotStatusInventoryService $depotStatusInventoryService;

    public function __construct(DepotStatusInventoryService $depotStatusInventoryService)
    {
        $this->depotStatusInventoryService = $depotStatusInventoryService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderRegisteredEvent::class => 'onOrderRegistered',
        ];
    }

    public function onOrderRegistered(OrderRegisteredEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getOrder();
        $this->depotStatusInventoryService->handle($order->getId());
    }
}
