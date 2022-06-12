<?php

namespace App\Service\OrderShipment\SystemChangeOrderShipmentStatus\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class SystemChangeOrderShipmentStatusException extends Exception
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;
}
