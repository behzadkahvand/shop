<?php

namespace App\Service\Layout\CacheBlock;

class CacheBlock extends CacheableBlockDecorator
{
    protected function getCacheTags(): array
    {
        return ['LAYOUT', 'BLOCK'];
    }
}
