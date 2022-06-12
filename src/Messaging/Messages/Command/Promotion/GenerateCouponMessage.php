<?php

namespace App\Messaging\Messages\Command\Promotion;

final class GenerateCouponMessage
{
    public function __construct(private int $couponGeneratorInstructionId)
    {
    }

    public function getCouponGeneratorInstructionId(): int
    {
        return $this->couponGeneratorInstructionId;
    }
}
