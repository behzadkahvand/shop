<?php

namespace App\Service\Order\Stages;

use App\Entity\CartItem;
use App\Entity\OrderItem;
use App\Service\OrderItemLog\OrderItemLogCreator;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

class StoreOrderItemStage implements TagAwarePipelineStageInterface
{
    private OrderItemLogCreator $orderItemLogCreator;

    public function __construct(OrderItemLogCreator $orderItemLogCreator)
    {
        $this->orderItemLogCreator = $orderItemLogCreator;
    }

    public function __invoke(AbstractPipelinePayload $payload)
    {
        $cart = $payload->getCart();
        $order = $payload->getOrder();
        $manager = $payload->getEntityManager();

        $orderSubtotal = 0;
        $orderGrandTotal = 0;
        /**
         * @var CartItem $cartItem
         */
        foreach ($cart->getCartItems() as $cartItem) {
            $inventory = $cartItem->getInventory();
            $orderItem = (new OrderItem())
                ->setSubtotal($cartItem->getSubtotal())
                ->setGrandTotal($cartItem->getGrandTotal())
                ->setInventory($inventory)
                ->setPrice($cartItem->getPrice())
                ->setFinalPrice($cartItem->getFinalPrice())
                ->setQuantity($cartItem->getQuantity())
                ->setLeadTime($inventory->getLeadTime());

            $order->addOrderItem($orderItem);

            $manager->persist($orderItem);

            $this->orderItemLogCreator->create($orderItem, 0, $orderItem->getQuantity());

            $orderSubtotal += $orderItem->getSubtotal();
            $orderGrandTotal += $orderItem->getGrandTotal();
        }

        $payload->getOrder()->setSubtotal($orderSubtotal);
        $payload->getOrder()->setGrandTotal($orderGrandTotal);

        return $payload;
    }

    public static function getPriority(): int
    {
        return 90;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }
}
