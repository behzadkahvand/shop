<?php

namespace App\Validator\Promotion;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CouponGeneratorInstruction extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'The value "{{ value }}" is not valid.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
