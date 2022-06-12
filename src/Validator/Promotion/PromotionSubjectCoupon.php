<?php

namespace App\Validator\Promotion;

use App\Service\Promotion\PromotionSubjectInterface;
use Symfony\Component\Validator\Constraint;

class PromotionSubjectCoupon extends Constraint
{
    /** @var string */
    public $message = 'این کد تخفیف معتبر نمی باشد. ';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
