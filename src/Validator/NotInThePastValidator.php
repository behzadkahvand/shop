<?php

namespace App\Validator;

use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotInThePastValidator extends ConstraintValidator
{
    /**
     * @param ?DateTime      $value
     * @param \App\Validator\NotInThePast $constraint
     *
     * @return bool|void
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($this->isInThePast($value)) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }

        return true;
    }

    private function isInThePast(DateTime $date): bool
    {
        return $date->format('Y-m-d') < date('Y-m-d');
    }
}
