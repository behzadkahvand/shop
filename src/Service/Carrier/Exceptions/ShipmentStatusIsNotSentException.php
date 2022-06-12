<?php

namespace App\Service\Carrier\Exceptions;

use App\Service\Condition\Exceptions\ConditionException;

class ShipmentStatusIsNotSentException extends ConditionException
{
    protected $message = 'The selected shipment status should be sent!';
}
