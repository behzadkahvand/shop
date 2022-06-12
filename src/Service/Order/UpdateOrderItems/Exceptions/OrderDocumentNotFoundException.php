<?php

namespace App\Service\Order\UpdateOrderItems\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class OrderDocumentNotFoundException extends UpdateOrderItemsException
{
    protected $code = Response::HTTP_NOT_FOUND;

    protected $message = 'Order document is not found!';
}
