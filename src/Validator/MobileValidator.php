<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MobileValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     *
     * @return bool|void
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint Mobile */

        if ($value === null || $value === '') {
            return;
        }

        if (
            (bool) preg_match('/^((09)[\d]{9})+$/', $value)
        ) {
            return true;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
