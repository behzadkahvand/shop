<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\PromotionCoupon;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;

class PromotionCouponCustomerEligibilityChecker implements PromotionCouponEligibilityCheckerInterface
{
    private ContextOperationManager $contextOperationManager;

    public function __construct(ContextOperationManager $contextOperationManager)
    {
        $this->contextOperationManager = $contextOperationManager;
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, PromotionCoupon $promotionCoupon, array &$context = []): bool
    {
        if ($promotionCoupon->getCustomers()->count() < 1) {
            return true;
        }

        $valid = $promotionCoupon->containsCustomer($promotionSubject->getCustomer());

        if (!$valid) {
            $this->contextOperationManager->addErrorMessage(
                $context,
                'این کد تخفیف برای شما معتبر نمی باشد. '
            );
        }

        return $valid;
    }
}
