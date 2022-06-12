<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BankingCardValidator extends ConstraintValidator
{
    /**
     * @param BankingCard|null $value
     * @param Constraint $constraint
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BankingCard) {
            throw new UnexpectedTypeException($constraint, BankingCard::class);
        }

        if (null === $value) {
            return;
        }

        if (! preg_match('/^\d{16}$/', $value)) {
            $this->addViolation($constraint, $value);

            return;
        }

        $sum = 0;
        for ($position = 1; $position <= 16; $position++) {
            $temp = $value[$position - 1];
            $temp = $position % 2 === 0 ? $temp : $temp * 2;
            $temp = $temp > 9 ? $temp - 9 : $temp;

            $sum += $temp;
        }

        if ($sum % 10 === 0) {
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
