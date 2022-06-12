<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\Order;
use App\Entity\PromotionCoupon;
use App\Repository\OrderRepository;
use App\Service\Promotion\PromotionSubjectInterface;

final class PromotionCouponUsageLimitEligibilityChecker implements PromotionCouponEligibilityCheckerInterface
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, PromotionCoupon $promotionCoupon, array &$context = []): bool
    {
        $usageLimit = $promotionCoupon->getUsageLimit();

        if ($usageLimit === null) {
            return true;
        }

        $excludedOrder = null;
        if ($promotionSubject instanceof Order) {
            $excludedOrder = $promotionSubject;
        }

        // TODO: use the `used` property for better performance
        $placedOrdersNumber = $this->orderRepository->countByCoupon($promotionCoupon, $excludedOrder);

        return $placedOrdersNumber < $usageLimit;
    }
}
