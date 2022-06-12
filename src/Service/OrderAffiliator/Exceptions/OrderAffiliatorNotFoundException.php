<?php

namespace App\Service\OrderAffiliator\Exceptions;

final class OrderAffiliatorNotFoundException extends OrderAffiliatorException
{
    protected $message = 'Order affiliator not found!';
}
