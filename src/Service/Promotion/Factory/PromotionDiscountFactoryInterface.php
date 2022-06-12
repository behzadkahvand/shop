<?php

namespace App\Service\Promotion\Factory;

use App\Entity\PromotionAction;
use App\Entity\PromotionDiscount;
use App\Service\Promotion\PromotionSubjectInterface;

interface PromotionDiscountFactoryInterface
{
    public static function supportedSubjectClass(): string;

    public function create(PromotionAction $action, int $amount, PromotionSubjectInterface $subject): PromotionDiscount;
}
