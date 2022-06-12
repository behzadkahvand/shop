<?php

namespace App\Service\Condition\Exceptions;

class InventoryIsNotConfirmedException extends ConditionException
{
    protected $message = 'The selected inventory is not confirmed!';
}
