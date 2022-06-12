<?php

namespace App\Messaging\Messages\Command\Product;

class BatchImportDigikalaProducts
{
    public function __construct(protected string $url, protected ?int $sellerId = null, protected ?string $digikalaSellerId = null)
    {
    }

    public function getUrl(): string
    {
        return $this->url;
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
