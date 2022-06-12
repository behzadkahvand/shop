<?php

namespace App\Service\Carrier\Exceptions;

use App\Service\Condition\Exceptions\ConditionException;

class ShipmentAlreadyHasBeenPaidException extends ConditionException
{
    protected $message = 'The selected shipment already has been paid!';
}
