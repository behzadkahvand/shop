<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;

class PromotionDurationEligibilityChecker implements PromotionEligibilityCheckerInterface
{
    private ContextOperationManager $contextOperationManager;

    public function __construct(ContextOperationManager $contextOperationManager)
    {
        $this->contextOperationManager = $contextOperationManager;
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, Promotion $promotion, array &$context = []): bool
    {
        $now = new \DateTime();

        $startsAt = $promotion->getStartsAt();
        if (null !== $startsAt && $now < $startsAt) {
            return false;
        }

        $endsAt = $promotion->getEndsAt();
        if (null !== $endsAt && $now > $endsAt) {
            $this->contextOperationManager->addErrorMessage($context, 'این کد تخفیف منقضی شده است.');
            return false;
        }

        return true;
    }
}
