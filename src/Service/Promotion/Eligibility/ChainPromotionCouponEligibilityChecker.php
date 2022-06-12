<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\PromotionCoupon;
use App\Service\Promotion\PromotionSubjectInterface;
use Traversable;
use Webmozart\Assert\Assert;

class ChainPromotionCouponEligibilityChecker implements PromotionCouponEligibilityCheckerInterface
{
    /** @var PromotionCouponEligibilityCheckerInterface[]|iterable */
    private iterable $promotionCouponEligibilityCheckers;

    public function __construct(iterable $promotionCouponEligibilityCheckers)
    {
        $this->promotionCouponEligibilityCheckers = $promotionCouponEligibilityCheckers;

        Assert::notEmpty($promotionCouponEligibilityCheckers);
        Assert::allIsInstanceOf($promotionCouponEligibilityCheckers, PromotionCouponEligibilityCheckerInterface::class);
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, PromotionCoupon $promotionCoupon, array &$context = []): bool
    {
        foreach ($this->promotionCouponEligibilityCheckers as $promotionCouponEligibilityChecker) {
            if (!$promotionCouponEligibilityChecker->isEligible($promotionSubject, $promotionCoupon, $context)) {
                return false;
            }
        }

        return true;
    }
}
