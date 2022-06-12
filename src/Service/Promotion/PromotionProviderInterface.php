<?php

namespace App\Service\Promotion;

use App\Entity\Promotion;

interface PromotionProviderInterface
{
    /**
     * @param PromotionSubjectInterface $subject
     *
     * @return array|Promotion[]
     */
    public function getPromotions(PromotionSubjectInterface $subject): array;
}
