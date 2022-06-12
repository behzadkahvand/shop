<?php

namespace App\Service\Promotion\Action\DiscountValidation;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderPromotionDiscount;
use App\Entity\OrderShipment;
use App\Entity\PromotionDiscount;
use App\Service\Promotion\PromotionSubjectInterface;
use Webmozart\Assert\Assert;

class OrderPromotionDiscountValidator implements ConditionalDiscountValidatorInterface
{
    public function supports(PromotionSubjectInterface $promotionSubject): bool
    {
        return $promotionSubject instanceof Order;
    }

    /**
     * @param PromotionSubjectInterface|Order $promotionSubject
     * @param array $context
     *
     * @return bool
     */
    public function shouldApply(PromotionSubjectInterface $promotionSubject, array $context = []): bool
    {
        if (!isset($context['orderItem']) || !$context['orderItem'] instanceof OrderItem) {
            return false;
        }

        if (
            isset($context['inventory_ids']) &&
            is_array($context['inventory_ids']) &&
            !in_array($context['orderItem']->getInventory()->getId(), $context['inventory_ids'])
        ) {
            return false;
        }

        /**
         * @var OrderItem $orderItem
         */
        $orderItem = $context['orderItem'];
        /**
         * @var OrderShipment $shipment
         */
        $shipment = $orderItem->getOrderShipment();

        return $shipment->getDiscountsCount() === 0 &&
            in_array($shipment->getStatus(), [
                OrderShipmentStatus::NEW,
                OrderShipmentStatus::WAITING_FOR_SUPPLY,
                OrderShipmentStatus::PREPARING,
                OrderShipmentStatus::WAREHOUSE,
            ], true);
    }

    /**
     * @param OrderPromotionDiscount|PromotionDiscount $promotionDiscount
     *
     * @return bool
     */
    public function shouldRevert(PromotionDiscount $promotionDiscount): bool
    {
        Assert::isInstanceOf($promotionDiscount, OrderPromotionDiscount::class);

        $orderItem = $promotionDiscount->getOrderItem();

        if (!$orderItem) {
            return false;
        }

        $shipment = $orderItem->getOrderShipment();

        if (!$shipment) {
            return false;
        }

        return in_array($shipment->getStatus(), [
            OrderShipmentStatus::NEW,
            OrderShipmentStatus::WAITING_FOR_SUPPLY,
            OrderShipmentStatus::PREPARING,
            OrderShipmentStatus::CANCELED,
            OrderShipmentStatus::WAREHOUSE,
        ], true);
    }
}
