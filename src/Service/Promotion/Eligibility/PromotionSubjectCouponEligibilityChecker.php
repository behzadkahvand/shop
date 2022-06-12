<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Service\Promotion\PromotionSubjectInterface;

class PromotionSubjectCouponEligibilityChecker implements PromotionEligibilityCheckerInterface
{
    private PromotionCouponEligibilityCheckerInterface $promotionCouponEligibilityChecker;

    public function __construct(PromotionCouponEligibilityCheckerInterface $promotionCouponEligibilityChecker)
    {
        $this->promotionCouponEligibilityChecker = $promotionCouponEligibilityChecker;
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, Promotion $promotion, array &$context = []): bool
    {
        if (!$promotion->getCouponBased()) {
            return true;
        }

        $promotionCoupon = $promotionSubject->getPromotionCoupon();
        if (null === $promotionCoupon) {
            return false;
        }

        if ($promotion->getId() !== $promotionCoupon->getPromotion()->getId()) {
            return false;
        }

        return $this->promotionCouponEligibilityChecker->isEligible($promotionSubject, $promotionCoupon, $context);
    }
}
