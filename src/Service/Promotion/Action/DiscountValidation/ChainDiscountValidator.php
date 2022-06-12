<?php

namespace App\Service\Promotion\Action\DiscountValidation;

use App\Entity\PromotionDiscount;
use App\Service\Promotion\PromotionSubjectInterface;

class ChainDiscountValidator implements DiscountValidatorInterface
{
    /**
     * @var iterable|ConditionalDiscountValidatorInterface[]
     */
    private iterable $conditionalDiscountValidators;

    public function __construct(iterable $conditionalDiscountValidators)
    {
        $this->conditionalDiscountValidators = $conditionalDiscountValidators;
    }

    public function shouldApply(PromotionSubjectInterface $promotionSubject, array $context = []): bool
    {
        foreach ($this->conditionalDiscountValidators as $validator) {
            if (!$validator->supports($promotionSubject)) {
                continue;
            }

            if (!$validator->shouldApply($promotionSubject, $context)) {
                return false;
            }
        }

        return true;
    }

    public function shouldRevert(PromotionDiscount $promotionDiscount): bool
    {
        foreach ($this->conditionalDiscountValidators as $validator) {
            if (!$validator->supports($promotionDiscount->getSubject())) {
                continue;
            }

            if (!$validator->shouldRevert($promotionDiscount)) {
                return false;
            }
        }

        return true;
    }
}
