<?php

namespace App\Service\Promotion\Action\DiscountValidation;

use App\Entity\PromotionDiscount;
use App\Service\Promotion\PromotionSubjectInterface;

interface DiscountValidatorInterface
{
    public function shouldApply(PromotionSubjectInterface $promotionSubject, array $context = []): bool;

    public function shouldRevert(PromotionDiscount $promotionDiscount): bool;
}
