<?php

namespace App\Service\Promotion\Action;

use App\Entity\Promotion;
use App\Entity\PromotionAction;
use App\Form\Promotion\ActionConfiguration\FixedDiscountFormType;
use App\Service\Promotion\Action\DiscountValidation\DiscountValidatorInterface;
use App\Service\Promotion\PromotionSubjectInterface;
use Webmozart\Assert\Assert;

class FixedDiscountActionType implements ActionTypeInterface
{
    private DiscountCreatorInterface $discountCreator;

    private DiscountValidatorInterface $discountValidator;

    public function __construct(
        DiscountCreatorInterface $discountCreator,
        DiscountValidatorInterface $discountValidator
    ) {
        $this->discountCreator = $discountCreator;
        $this->discountValidator = $discountValidator;
    }

    public static function getName(): string
    {
        return 'fixed_discount';
    }

    public function getConfigurationFormType(): string
    {
        return FixedDiscountFormType::class;
    }

    public function apply(PromotionSubjectInterface $subject, PromotionAction $action, Promotion $promotion, array &$context = []): bool
    {
        if (0 === $subject->getItemsCount()) {
            return false;
        }

        $configuration = $action->getConfiguration();

        Assert::keyExists($configuration, 'amount');
        Assert::integer($configuration['amount']);

        $promotionAmount = min($subject->getPromotionSubjectTotal(), $configuration['amount']);

        if (0 === $promotionAmount) {
            return false;
        }

        $discounts = $this->discountCreator->create($action, $subject, $context);

        return !empty($discounts);
    }

    public function revert(PromotionSubjectInterface $subject, PromotionAction $action, Promotion $promotion, array &$context = []): bool
    {
        $allRemoved = true;
        foreach ($subject->getDiscounts() as $promotionDiscount) {
            if ($promotionDiscount->getAction()->getId() !== $action->getId()) {
                continue;
            }

            if (!$this->discountValidator->shouldRevert($promotionDiscount)) {
                $allRemoved = false;
                continue;
            }

            $subject->removeDiscount($promotionDiscount);
        }

        return $allRemoved;
    }
}
