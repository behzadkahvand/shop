<?php

namespace App\Service\Cart\Exceptions;

class CartItemNotFoundException extends CartException
{
    protected $message = 'Selected cart item not found!';
}
