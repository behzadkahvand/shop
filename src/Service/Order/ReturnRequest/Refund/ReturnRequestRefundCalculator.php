<?php

namespace App\Service\Order\ReturnRequest\Refund;

use App\Entity\ReturnRequest;
use App\Entity\ReturnRequestItem;

class ReturnRequestRefundCalculator
{
    public function calculate(ReturnRequest $request): void
    {
        $items = $request->getItems();
        foreach ($items as $item) {
            $this->setRefundAmount($item);
        }
    }

    private function setRefundAmount(ReturnRequestItem $item): void
    {
        $orderItemValue    = $this->calculateOrderItemValue($item);
        $totalRefundAmount = $orderItemValue * $item->getQuantity();

        $item->setRefundAmount($totalRefundAmount);
    }

    private function calculateOrderItemValue(ReturnRequestItem $item): int
    {
        $orderItem       = $item->getOrderItem();
        $totalDiscount   = $orderItem->getDiscountAmount();
        $quantity        = $orderItem->getQuantity();
        $discountPerItem = $totalDiscount / $quantity;
        $finalPrice      = $orderItem->getGrandTotal() / $quantity;

        return $finalPrice - $discountPerItem;
    }
}
