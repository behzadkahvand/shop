<?php

namespace App\Messaging\Messages\Command\Product;

final class LogInventory
{
    public function __construct(protected array $loggableProperties)
    {
    }

    public function getLoggableProperties(): array
    {
        return $this->loggableProperties;
    }
}
