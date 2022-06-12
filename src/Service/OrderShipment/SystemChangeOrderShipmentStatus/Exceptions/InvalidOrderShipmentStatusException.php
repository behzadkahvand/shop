<?php

namespace App\Service\OrderShipment\SystemChangeOrderShipmentStatus\Exceptions;

class InvalidOrderShipmentStatusException extends SystemChangeOrderShipmentStatusException
{
    protected $message = 'Order shipment status is invalid!';
}
