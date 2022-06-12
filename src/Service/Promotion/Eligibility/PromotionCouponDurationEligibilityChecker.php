<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\PromotionCoupon;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;

class PromotionCouponDurationEligibilityChecker implements PromotionCouponEligibilityCheckerInterface
{
    private ContextOperationManager $contextOperationManager;

    public function __construct(ContextOperationManager $contextOperationManager)
    {
        $this->contextOperationManager = $contextOperationManager;
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, PromotionCoupon $promotionCoupon, array &$context = []): bool
    {
        $endsAt = $promotionCoupon->getExpiresAt();

        $valid = $endsAt === null || new \DateTime() < $endsAt;

        if (!$valid) {
            $this->contextOperationManager->addErrorMessage($context, 'این کد تخفیف منقضی شده است.');
        }

        return $valid;
    }
}
