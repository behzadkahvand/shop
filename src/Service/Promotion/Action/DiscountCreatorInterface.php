<?php

namespace App\Service\Promotion\Action;

use App\Entity\PromotionAction;
use App\Entity\PromotionDiscount;
use App\Service\Promotion\PromotionSubjectInterface;

interface DiscountCreatorInterface
{
    /**
     * @param PromotionAction $action
     * @param PromotionSubjectInterface $subject
     * @param array $context
     *
     * @return array<PromotionDiscount>
     */
    public function create(PromotionAction $action, PromotionSubjectInterface $subject, array &$context = []): array;
}
