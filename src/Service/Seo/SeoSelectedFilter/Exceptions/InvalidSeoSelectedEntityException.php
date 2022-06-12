<?php

namespace App\Service\Seo\SeoSelectedFilter\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InvalidSeoSelectedEntityException extends Exception
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    protected $message = 'Seo selected entity is invalid!';
}
