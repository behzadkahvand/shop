<?php

namespace App\Service\Carrier\Exceptions;

use App\Service\Condition\Exceptions\ConditionException;

class CODPriceIsNotEquivalentToShipmentPayableException extends ConditionException
{
    protected $message = 'The amount is not equivalent to the selected shipment payable price!';
}
