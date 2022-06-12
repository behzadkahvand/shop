<?php

namespace App\Messaging\Messages\Command\Product;

final class LogSearch
{
    public function __construct(private string $term, private int $resultCount, private ?int $customerId)
    {
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function getResultCount(): int
    {
        return $this->resultCount;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }
}
