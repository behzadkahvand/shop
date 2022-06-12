<?php

namespace App\Messaging\Messages\Command\Product;

class ImportProductFromDigikala
{
    public function __construct(protected string $digikalaDkp, protected ?int $sellerId = null, protected ?string $digikalaSellerId = null)
    {
    }

    public function getDigikalaDkp(): string
    {
        return $this->digikalaDkp;
    }

    public function getSellerId(): ?int
    {
        return $this->sellerId;
    }

    public function getDigikalaSellerId(): ?string
    {
        return $this->digikalaSellerId;
    }
}
