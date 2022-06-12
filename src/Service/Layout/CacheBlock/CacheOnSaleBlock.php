<?php

namespace App\Service\Layout\CacheBlock;

class CacheOnSaleBlock extends CacheableBlockDecorator
{
    protected function getCacheTags(): array
    {
        return ['ON_SALE_LAYOUT', 'ON_SALE_BLOCK'];
    }
}
