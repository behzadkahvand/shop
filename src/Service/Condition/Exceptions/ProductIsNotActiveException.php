<?php

namespace App\Service\Condition\Exceptions;

class ProductIsNotActiveException extends ConditionException
{
    protected $message = 'The selected product is not active!';
}
