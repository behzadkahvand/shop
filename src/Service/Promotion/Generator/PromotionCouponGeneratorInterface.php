<?php

namespace App\Service\Promotion\Generator;

use App\Entity\CouponGeneratorInstruction;

interface PromotionCouponGeneratorInterface
{
    public function generate(CouponGeneratorInstruction $instruction): array;
}
