<?php

namespace App\Service\Promotion\Action;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\PromotionAction;
use App\Service\Promotion\Action\DiscountValidation\DiscountValidatorInterface;
use App\Service\Promotion\PromotionSubjectInterface;

class FixedDiscountDistributor implements OrderItemDiscountDistributorInterface
{
    private DiscountValidatorInterface $discountValidator;

    public function __construct(DiscountValidatorInterface $discountValidator)
    {
        $this->discountValidator = $discountValidator;
    }

    public function distributeForOrder(
        Order $subject,
        PromotionAction $action,
        array &$context
    ): array {
        $orderItemDiscounts = [];
        $totalOrderItemsDiscount = 0;
        $configuration = $action->getConfiguration();
        $amount = min($subject->getPromotionSubjectTotal(), $configuration['amount']);

        if (0 === $amount) {
            return [];
        }
        foreach ($subject->getOrderItems() as $orderItem) {
            $validatorContext = array_merge($context, ['orderItem' => $orderItem]);
            if (!$this->discountValidator->shouldApply($subject, $validatorContext)) {
                continue;
            }

            $remainingDiscountAmount = $amount - $totalOrderItemsDiscount;

            if ($remainingDiscountAmount <= 0) {
                break;
            }
            $orderItemDiscountAmount = (int) ($orderItem->getGrandTotal() / $subject->getGrandTotal() * $amount);
            $orderItemDiscountAmount = min($orderItemDiscountAmount, $remainingDiscountAmount);

            $orderItemDiscounts[] = [
                'orderItem' => $orderItem,
                'discountAmount' => $orderItemDiscountAmount,
            ];
        }

        $totalOrderItemsDiscount = array_reduce($orderItemDiscounts, function ($carry, $item) {
            $carry += $item['discountAmount'];

            return $carry;
        }, 0);

        if ($totalOrderItemsDiscount < $amount && count($orderItemDiscounts) > 0) {
            $orderItemDiscounts[0]['discountAmount'] += $amount - $totalOrderItemsDiscount;
        }

        return $orderItemDiscounts;
    }

    public function calculateAmountForCart(Cart $subject, PromotionAction $action, array &$context): int
    {
        $configuration = $action->getConfiguration();
        return min($subject->getPromotionSubjectTotal(), $configuration['amount']);
    }
}
