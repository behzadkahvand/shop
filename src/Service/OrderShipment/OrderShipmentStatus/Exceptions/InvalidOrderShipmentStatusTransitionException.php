<?php

namespace App\Service\OrderShipment\OrderShipmentStatus\Exceptions;

class InvalidOrderShipmentStatusTransitionException extends OrderShipmentStatusException
{
    protected $message = 'Order shipment status transition is invalid!';
}
