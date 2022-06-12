<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AddressValidator extends ConstraintValidator
{
    /**
     * @param mixed      $value
     * @param Constraint $constraint
     *
     * @return bool|void
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint Address */

        if ($value === null || $value === '') {
            return;
        }

        if (
            (bool) preg_match(
                "/^[^\s][\pL\s\d\-\/\,\،\.\\\\\x{200c}\x{064b}\x{064d}\x{064c}\x{064e}\x{064f}\x{0650}\x{0651}\x{6F0}-\x{6F9}][^\s$]*/u",
                $value
            )
        ) {
            return true;
        }

        $this->context->buildViolation($constraint->message)
                      ->setParameter('{{ value }}', $value)
                      ->addViolation();
    }
}
