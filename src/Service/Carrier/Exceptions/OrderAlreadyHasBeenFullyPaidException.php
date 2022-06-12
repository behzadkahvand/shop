<?php

namespace App\Service\Carrier\Exceptions;

use App\Service\Condition\Exceptions\ConditionException;

class OrderAlreadyHasBeenFullyPaidException extends ConditionException
{
    protected $message = 'The selected order already has been fully paid!';
}
