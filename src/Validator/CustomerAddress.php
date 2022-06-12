<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class CustomerAddress extends Constraint
{
    public $message = 'Address does not belong to current user.';
}
