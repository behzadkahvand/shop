<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Repository\OrderRepository;
use App\Service\Promotion\PromotionSubjectInterface;

class PromotionUsageLimitEligibilityChecker implements PromotionEligibilityCheckerInterface
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, Promotion $promotion, array &$context = []): bool
    {
        if (null === $usageLimit = $promotion->getUsageLimit()) {
            return true;
        }

        // TODO: use the `used` property for better performance
        $placedOrdersNumber = $this->orderRepository->countByPromotion($promotion);

        return $placedOrdersNumber < $usageLimit;
    }
}
