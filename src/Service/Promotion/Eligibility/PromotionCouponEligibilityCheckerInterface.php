<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\PromotionCoupon;
use App\Service\Promotion\PromotionSubjectInterface;

interface PromotionCouponEligibilityCheckerInterface
{
    public function isEligible(PromotionSubjectInterface $promotionSubject, PromotionCoupon $promotionCoupon, array &$context = []): bool;
}
