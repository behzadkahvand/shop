<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Service\Promotion\PromotionSubjectInterface;

class WalletEligibilityChecker implements PromotionEligibilityCheckerInterface
{
    public function isEligible(PromotionSubjectInterface $promotionSubject, Promotion $promotion, array &$context = []): bool
    {
        if ($this->customerHasWalletBalance($promotionSubject)) {
            $this->setErrorMessage($context);

            return false;
        }

        return true;
    }

    private function customerHasWalletBalance(PromotionSubjectInterface $promotionSubject): bool
    {
        return $promotionSubject->getCustomer()->getWalletBalance() > 0;
    }

    private function setErrorMessage(array &$context)
    {
        $context['error_messages'] = ['امکان استفاده از کد تخفیف وقتی حساب کاربری دارای موجودی می‌باشد وجود ندارد.'];
    }
}
