<?php

namespace App\Service\Promotion\Action;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\PromotionAction;

interface OrderItemDiscountDistributorInterface
{
    public function distributeForOrder(
        Order $subject,
        PromotionAction $action,
        array &$context
    ): array;

    public function calculateAmountForCart(
        Cart $subject,
        PromotionAction $action,
        array &$context
    ): int;
}
