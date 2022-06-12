<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PersianCharsValidator
 */
final class PersianEnglishCharsValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PersianEnglishChars) {
            throw new UnexpectedTypeException($constraint, PersianEnglishChars::class);
        }


        if (
            (bool)preg_match(
                "/^[\pL\s\x{200c}\x{064b}\x{064d}\x{064c}\x{064e}\x{064f}\x{0650}\x{0651}]+$/u",
                $value
            )
        ) {
            return true;
        }

        $this->context->buildViolation($constraint->messages)->addViolation();
    }
}
