<?php

namespace App\Exceptions;

class CacheKeyNotFoundException extends \Exception
{
    protected $message = 'Key not found!';
}
