<?php

namespace App\Service\Promotion\Action\DiscountValidation;

use App\Service\Promotion\PromotionSubjectInterface;

interface ConditionalDiscountValidatorInterface extends DiscountValidatorInterface
{
    public function supports(PromotionSubjectInterface $promotionSubject): bool;
}
