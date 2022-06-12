<?php

namespace App\Service\Seller\SellerOrderItem\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class SellerOrderItemIsRejectedException
 */
final class SellerOrderItemIsRejectedException extends HttpException
{
    public function __construct()
    {
        parent::__construct(Response::HTTP_FORBIDDEN, 'Operation not allowed!');
    }
}
