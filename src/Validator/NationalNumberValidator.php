<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NationalNumberValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     *
     * @return bool|void
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint NationalNumber */

        if (null === $value || '' === $value) {
            return true;
        }

        if (
            !preg_match('/^\d{8,10}$/', $value) || preg_match(
                '/^[0]{10}|[1]{10}|[2]{10}|[3]{10}|[4]{10}|[5]{10}|[6]{10}|[7]{10}|[8]{10}|[9]{10}$/',
                $value
            )
        ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        $sub = 0;

        if (8 == strlen($value)) {
            $value = '00' . $value;
        } elseif (9 == strlen($value)) {
            $value = '0' . $value;
        }

        for ($i = 0; $i <= 8; ++$i) {
            $sub = $sub + ($value[$i] * (10 - $i));
        }

        if (($sub % 11) < 2) {
            $control = ($sub % 11);
        } else {
            $control = 11 - ($sub % 11);
        }

        if ($value[9] == $control) {
            return true;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
