<?php

namespace App\Service\ProductVariant\Exceptions;

/**
 * Class ProductIdentifierException
 */
final class ProductIdentifierException extends ProductVariantException
{
    public static function creatingInventoryNotAllowed(int $id): self
    {
        $msg = "Product with id {$id} require at least 1 product identifier. Creating inventory not allowed!";

        return new self($msg);
    }
}
