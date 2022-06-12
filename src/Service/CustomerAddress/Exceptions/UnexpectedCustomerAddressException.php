<?php

namespace App\Service\CustomerAddress\Exceptions;

class UnexpectedCustomerAddressException extends CustomerAddressException
{
    protected $message = 'Customer address is unexpected!';
}
