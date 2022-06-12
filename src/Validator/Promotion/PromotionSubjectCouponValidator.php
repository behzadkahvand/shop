<?php

namespace App\Validator\Promotion;

use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\Eligibility\PromotionEligibilityCheckerInterface;
use App\Service\Promotion\PromotionSubjectInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class PromotionSubjectCouponValidator extends ConstraintValidator
{
    private PromotionEligibilityCheckerInterface $promotionEligibilityChecker;

    private ContextOperationManager $contextOperationManager;

    public function __construct(
        PromotionEligibilityCheckerInterface $promotionEligibilityChecker,
        ContextOperationManager $contextOperationManager
    ) {
        $this->promotionEligibilityChecker = $promotionEligibilityChecker;
        $this->contextOperationManager = $contextOperationManager;
    }

    public function validate($value, Constraint $constraint): void
    {
        /** @var PromotionSubjectCoupon $constraint */
        Assert::isInstanceOf($constraint, PromotionSubjectCoupon::class);

        if (!$value instanceof PromotionSubjectInterface) {
            return;
        }

        $promotionCoupon = $value->getPromotionCoupon();
        if ($promotionCoupon === null) {
            return;
        }

        $value->updateTotals();

        $context = [];
        if ($this->promotionEligibilityChecker->isEligible($value, $promotionCoupon->getPromotion(), $context)) {
            return;
        }

        $message = $this->contextOperationManager->getFirstErrorMessage($context) ?: $constraint->message;
        $this->context->buildViolation($message)->atPath('promotionCoupon')->addViolation();
    }
}
