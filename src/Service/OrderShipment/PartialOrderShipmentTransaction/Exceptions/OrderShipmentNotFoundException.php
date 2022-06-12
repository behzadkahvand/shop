<?php

namespace App\Service\OrderShipment\PartialOrderShipmentTransaction\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class OrderShipmentNotFoundException extends PartialOrderShipmentTransactionException
{
    protected $code = Response::HTTP_NOT_FOUND;

    protected $message = 'Order shipment is not found for create transaction action!';
}
