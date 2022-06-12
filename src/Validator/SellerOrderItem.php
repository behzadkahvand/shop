<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class SellerOrderItem extends Constraint
{
    public $message = 'Item does not belong to current user.';
}
