<?php

namespace App\Service\OrderAffiliator\Exceptions;

final class InvalidUtmSourceException extends OrderAffiliatorException
{
    protected $message = 'Affiliator utm source is invalid!';
}
