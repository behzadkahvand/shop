<?php

namespace App\Service\Condition\Exceptions;

class InventoryIsNotActiveException extends ConditionException
{
    protected $message = 'The selected inventory is not active!';
}
