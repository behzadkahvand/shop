<?php

namespace App\Service\Cart\Exceptions;

class CartNotFoundException extends CartException
{
    protected $message = 'Selected cart not found!';
}
