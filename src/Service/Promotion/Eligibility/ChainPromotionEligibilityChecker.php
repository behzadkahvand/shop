<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Service\Promotion\PromotionSubjectInterface;

class ChainPromotionEligibilityChecker implements PromotionEligibilityCheckerInterface
{
    /**
     * @var iterable|PromotionEligibilityCheckerInterface[]
     */
    private iterable $promotionEligibilityCheckers;

    /**
     * @param iterable|PromotionEligibilityCheckerInterface[] $promotionEligibilityCheckers
     */
    public function __construct(iterable $promotionEligibilityCheckers)
    {
        $this->promotionEligibilityCheckers = $promotionEligibilityCheckers;
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, Promotion $promotion, array &$context = []): bool
    {
        foreach ($this->promotionEligibilityCheckers as $promotionEligibilityChecker) {
            if (!$promotionEligibilityChecker->isEligible($promotionSubject, $promotion, $context)) {
                return false;
            }
        }

        return true;
    }
}
