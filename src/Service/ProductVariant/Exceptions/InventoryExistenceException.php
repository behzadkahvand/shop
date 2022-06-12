<?php

namespace App\Service\ProductVariant\Exceptions;

class InventoryExistenceException extends ProductVariantException
{
    protected $message = 'Inventory Exists!';
}
