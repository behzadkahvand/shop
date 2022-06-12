<?php

namespace App\EventSubscriber\OrderItem;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Log\OrderLogService;
use App\Service\Order\DeleteOrderItem\Event\OrderItemRemoved;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\Order\UpdateOrderItems\Event\OrderItemsUpdated;
use App\Service\Order\UpdateOrderItems\Event\OrderItemUpdated;
use App\Service\Promotion\PromotionProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/*@TODO Unit test*/
class OrderItemSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PromotionProcessorInterface $promotionProcessor,
        private OrderLogService $orderLogService,
        private RecalculateOrderDocument $recalculateOrderDocument
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderItemRemoved::class => [
                ['onOrderItemRemoved', 10],
                ['onOrderItemSetDeletedBy', 11]
            ],
            OrderItemUpdated::class => 'onOrderItemUpdated',
            OrderItemsUpdated::class => 'onOrderItemsUpdated',
        ];
    }

    public function onOrderItemRemoved(OrderItemRemoved $event)
    {
        $order = $event->getOrder();
        $this->deleteDiscountsOfOrderItem($event->getOrderItem(), $order);

        $this->promotionProcessor->processChangedSubject($order);
    }

    public function onOrderItemUpdated(OrderItemUpdated $event)
    {
        $orderItem = $event->getOrderItem();
        if ($orderItem->getGrandTotal() < $event->getOldGrandTotal()) {
            $this->updateDiscountsOfOrderItem($event->getOrderItem(), $orderItem->getOrder());
        }
    }

    public function onOrderItemsUpdated(OrderItemsUpdated $event)
    {
        $order = $event->getOrder();
        $this->promotionProcessor->processChangedSubject($order);
    }

    private function deleteDiscountsOfOrderItem(OrderItem $orderItem, Order $order)
    {
        foreach ($orderItem->getDiscounts() as $discount) {
            $orderItem->removeDiscount($discount);
            $orderItem->getOrderShipment()->removeDiscount($discount);
            $order->removeDiscount($discount);
            $this->entityManager->remove($discount);
        }

        $this->recalculateOrderDocument->perform($order);
    }

    private function updateDiscountsOfOrderItem(OrderItem $orderItem, Order $order)
    {
        foreach ($orderItem->getDiscounts() as $discount) {
            $discountAmount = floor($orderItem->getQuantity() / $discount->getQuantity() * $discount->getAmount());
            $discount->setAmount($discountAmount);
            $discount->setQuantity($orderItem->getQuantity());
        }
    }

    public function onOrderItemSetDeletedBy(OrderItemRemoved $event)
    {
        $this->orderLogService->onOrderItemSetDeletedBy($event->getOrderItem()->getId(), $event->getAdmin());
    }
}
