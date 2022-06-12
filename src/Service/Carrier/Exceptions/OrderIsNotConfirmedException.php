<?php

namespace App\Service\Carrier\Exceptions;

use App\Service\Condition\Exceptions\ConditionException;

class OrderIsNotConfirmedException extends ConditionException
{
    protected $message = 'The selected order is not confirmed!';
}
