<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PhoneValidator extends ConstraintValidator
{
    /**
     * @param Phone|null $value
     * @param Constraint $constraint
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (! $constraint instanceof Phone) {
            throw new UnexpectedTypeException($constraint, Phone::class);
        }

        if ($value === null) {
            return;
        }

        if ((bool) preg_match('/^(0[1-9]{2})[2-9][0-9]{7}$/', $value)) {
            return;
        }

        $this->addViolation($constraint, $value);
    }

    /**
     * @param Constraint $constraint
     * @param mixed $value
     */
    private function addViolation(Constraint $constraint, $value): void
    {
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
