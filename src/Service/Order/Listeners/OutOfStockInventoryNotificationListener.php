<?php

namespace App\Service\Order\Listeners;

use App\Entity\Order;
use App\Events\Order\OrderRegisteredEvent;
use App\Service\Notification\DTOs\Seller\OnDemandInventoryIsOutOfStock;
use App\Service\Notification\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class OutOfStockInventoryNotificationListener implements EventSubscriberInterface
{
    public function __construct(private NotificationService $notificationService)
    {
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

        foreach ($order->getOrderItems() as $orderItem) {
            $inventory = $orderItem->getInventory();
            if ($inventory->getLeadTime() === 0 && $inventory->getSellerStock() === 0) {
                $this->notificationService->send(new OnDemandInventoryIsOutOfStock($inventory));
            }
        }
    }
}
