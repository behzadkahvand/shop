<?php

namespace App\Service\Product\Search\Exceptions;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class UnsupportedSearchDataTypeException extends InvalidArgumentException
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function __construct(string $supportedDataType, string $unsupportedDataType)
    {
        parent::__construct(sprintf('Expected instance of %s got %s', $supportedDataType, $unsupportedDataType));
    }
}
