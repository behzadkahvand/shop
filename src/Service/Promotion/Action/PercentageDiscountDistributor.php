<?php

namespace App\Service\Promotion\Action;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\PromotionAction;
use App\Service\Promotion\Action\DiscountValidation\DiscountValidatorInterface;
use App\Service\Promotion\PromotionSubjectInterface;

class PercentageDiscountDistributor implements OrderItemDiscountDistributorInterface
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
        $ratio = $configuration['ratio'] ?? 0;
        $maxAmount = $configuration['max_amount'] ?? null;
        foreach ($subject->getItems() as $orderItem) {
            $validatorContext = array_merge($context, ['orderItem' => $orderItem]);
            if (!$this->discountValidator->shouldApply($subject, $validatorContext)) {
                continue;
            }

            $remainingDiscountAmount = $maxAmount === null ? PHP_INT_MAX : $maxAmount - $totalOrderItemsDiscount;

            if ($remainingDiscountAmount <= 0) {
                break;
            }

            $orderItemDiscountAmount = (int) ($orderItem->getGrandTotal() * $ratio / 100);
            $orderItemDiscountAmount = min($orderItemDiscountAmount, $remainingDiscountAmount);

            $orderItemDiscounts[] = [
                'orderItem' => $orderItem,
                'discountAmount' => $orderItemDiscountAmount,
            ];
            $totalOrderItemsDiscount += $orderItemDiscountAmount;
        }

        return $orderItemDiscounts;
    }

    public function calculateAmountForCart(Cart $subject, PromotionAction $action, array &$context): int
    {
        $totalCartDiscount = 0;
        $configuration = $action->getConfiguration();
        $ratio = $configuration['ratio'] ?? 0;
        $maxAmount = $configuration['max_amount'] ?? null;
        foreach ($subject->getItems() as $cartItem) {
            if (
                isset($context['inventory_ids']) &&
                !is_array($context['inventory_ids']) &&
                in_array($cartItem->getInventory()->getId(), $context['inventory_ids'])
            ) {
                return false;
            }

            $remainingDiscountAmount = $maxAmount === null ? PHP_INT_MAX : $maxAmount - $totalCartDiscount;

            if ($remainingDiscountAmount <= 0) {
                break;
            }

            $orderItemDiscountAmount = (int) ($cartItem->getGrandTotal() * $ratio / 100);
            $totalCartDiscount += min($orderItemDiscountAmount, $remainingDiscountAmount);
        }

        return $totalCartDiscount;
    }
}
