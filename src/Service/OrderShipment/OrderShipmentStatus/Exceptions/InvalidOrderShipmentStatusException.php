<?php

namespace App\Service\OrderShipment\OrderShipmentStatus\Exceptions;

class InvalidOrderShipmentStatusException extends OrderShipmentStatusException
{
    protected $message = 'Order shipment status is invalid!';
}
