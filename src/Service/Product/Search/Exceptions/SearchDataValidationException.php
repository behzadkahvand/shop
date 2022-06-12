<?php

namespace App\Service\Product\Search\Exceptions;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SearchDataValidationException
 */
final class SearchDataValidationException extends InvalidArgumentException
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;
}
