<?php

namespace App\Service\Carrier\Exceptions;

use App\Service\Condition\Exceptions\ConditionException;

class OrderPaymentMethodIsNotOfflineException extends ConditionException
{
    protected $message = 'The selected order payment method should be offline!';
}
