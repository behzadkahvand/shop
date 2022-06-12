<?php

namespace App\Service\Layout\OnSaleBlock;

use App\Service\Layout\LayoutInterface;

interface OnSaleBlockInterface extends LayoutInterface
{
    public function getConfigCode(): string;
    public function findProducts(): array;
}
