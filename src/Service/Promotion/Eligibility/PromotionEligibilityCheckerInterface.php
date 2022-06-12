<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Service\Promotion\PromotionSubjectInterface;

interface PromotionEligibilityCheckerInterface
{
    public function isEligible(PromotionSubjectInterface $promotionSubject, Promotion $promotion, array &$context = []): bool;
}
