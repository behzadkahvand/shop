<?php

namespace App\Service\Order\RecalculateOrderDocument;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Service\Order\UpdateOrderItems\Exceptions\OrderDocumentNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

class RecalculateOrderDocument
{
    public function __construct(protected EntityManagerInterface $manager)
    {
    }

    /**
     * @param Order $order
     * @param bool $performFlush
     * @throws OrderDocumentNotFoundException
     */
    public function perform(Order $order, bool $performFlush = false): void
    {
        $grandTotal    = 0;
        $subTotal      = 0;
        $discountTotal = 0;

        $orderItems     = $order->getOrderItems();
        $orderShipments = $order->getShipments();

        if ($order->getStatus() !== OrderStatus::CANCELED) {
            $invalidSellerOrderItemStatuses = [
                SellerOrderItemStatus::CANCELED_BY_SELLER,
                SellerOrderItemStatus::CANCELED_BY_USER,
            ];

            $orderItems = $orderItems->filter(
                fn(OrderItem $orderItem): bool => !in_array($orderItem->getSellerOrderItem()->getStatus(), $invalidSellerOrderItemStatuses)
            );

            $invalidShipmentStatuses = [
                OrderShipmentStatus::CANCELED_BY_CUSTOMER,
                OrderShipmentStatus::CANCELED,
            ];

            $orderShipments = $orderShipments->filter(
                function (OrderShipment $orderShipment) use ($invalidShipmentStatuses): bool {
                    return $orderShipment->hasItems() && !in_array($orderShipment->getStatus(), $invalidShipmentStatuses);
                }
            );
        }

        foreach ($orderItems as $orderItem) {
            $grandTotal += $orderItem->getGrandTotal();
            $grandTotal -= $orderItem->getReturnedItemsRefundAmount();
            $subTotal   += $orderItem->getSubtotal();
        }

        foreach ($orderShipments as $shipment) {
            $grandTotal += $shipment->getGrandTotal();
            $subTotal   += $shipment->getSubtotal();
        }

        foreach ($order->getDiscounts() as $discount) {
            $discountTotal += $discount->getAmount();
        }

        $grandTotal -= $discountTotal;

        if ($grandTotal < 0) {
            $grandTotal = 0;
        }

        $order->setGrandTotal($grandTotal);
        $order->setSubtotal($subTotal);
        $order->setDiscountTotal($discountTotal);

        $order->getOrderDocumentOrFail()->setAmount($order->getGrandTotal());

        if ($performFlush) {
            $this->manager->flush();
        }
    }
}
