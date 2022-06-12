<?php

namespace App\Service\Promotion\Action;

use App\Entity\Promotion;
use App\Service\Promotion\PromotionSubjectInterface;

interface PromotionApplicatorInterface
{
    public function apply(PromotionSubjectInterface $subject, Promotion $promotion, array &$context = []): void;

    public function revert(PromotionSubjectInterface $subject, Promotion $promotion, array &$context = []): bool;
}
