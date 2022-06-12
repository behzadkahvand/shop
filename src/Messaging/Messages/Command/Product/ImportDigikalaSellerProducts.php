<?php

namespace App\Messaging\Messages\Command\Product;

class ImportDigikalaSellerProducts
{
    public function __construct(protected string $digikalaSellerId, protected int $sellerId)
    {
    }

    public function getDigikalaSellerId(): string
    {
        return $this->digikalaSellerId;
    }

    public function getSellerId(): int
    {
        return $this->sellerId;
    }
}
