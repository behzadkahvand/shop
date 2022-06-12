<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PersianCharsValidator
 */
final class PersianCharsValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PersianChars) {
            throw new UnexpectedTypeException($constraint, PersianChars::class);
        }

        if (null === $value) {
            return;
        }

        if ($constraint->alpha) {
            $matchPersianAlphaChars = (bool) preg_match(
                "/^[\x{600}-\x{6FF}\x{200c}\x{064b}\x{064d}\x{064c}\x{064e}\x{064f}\x{0650}\x{0651}\s]+$/u",
                $value
            );

            if (!$matchPersianAlphaChars) {
                $this->context->buildViolation($constraint->messages['alpha'])->addViolation();
            }
        }

        if ($constraint->num) {
            $matchPersianNumberChars = (bool) preg_match('/^[\x{6F0}-\x{6F9}]+$/u', $value);

            if (!$matchPersianNumberChars) {
                $this->context->buildViolation($constraint->messages['num'])->addViolation();
            }
        }

        if ($constraint->alphaNum) {
            $matchPersianAlphaNumChars = (bool) preg_match(
                '/^[\x{600}-\x{6FF}\x{200c}\x{064b}\x{064d}\x{064c}\x{064e}\x{064f}\x{0650}\x{0651}\s]+$/u',
                $value
            );

            if (!$matchPersianAlphaNumChars) {
                $this->context->buildViolation($constraint->messages['alpha_num'])->addViolation();
            }
        }
    }
}
