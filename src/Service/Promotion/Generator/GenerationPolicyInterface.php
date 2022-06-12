<?php

namespace App\Service\Promotion\Generator;

use App\Entity\CouponGeneratorInstruction;

interface GenerationPolicyInterface
{
    public function isGenerationPossible(CouponGeneratorInstruction $instruction): bool;

    public function getPossibleGenerationAmount(CouponGeneratorInstruction $instruction): int;
}
