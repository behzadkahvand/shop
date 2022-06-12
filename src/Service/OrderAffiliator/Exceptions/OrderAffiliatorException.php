<?php

namespace App\Service\OrderAffiliator\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class OrderAffiliatorException extends Exception
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;
}
