<?php

namespace App\Service\Promotion\Exception;

use App\Entity\CouponGeneratorInstruction;
use InvalidArgumentException;

final class FailedGenerationException extends InvalidArgumentException
{
    public function __construct(
        CouponGeneratorInstruction $instruction,
        int $exceptionCode = 0,
        ?\Exception $previousException = null
    ) {
        $message = sprintf(
            'Invalid coupon code length or coupons amount. It is not possible to generate %d unique coupons with %d code length',
            $instruction->getAmount(),
            $instruction->getCodeLength()
        );

        parent::__construct($message, $exceptionCode, $previousException);
    }
}
