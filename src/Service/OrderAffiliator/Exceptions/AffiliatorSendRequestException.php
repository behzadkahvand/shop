<?php

namespace App\Service\OrderAffiliator\Exceptions;

final class AffiliatorSendRequestException extends OrderAffiliatorException
{
    protected $message = 'There is a problem with affiliator sending request!';
}
