<?php

namespace App\Service\Layout\CacheBlock;

interface CacheableBlockInterface
{
    public const CACHE_PREFIX = 'LAYOUT_BLOCK_';

    /**
     * Returns the time ( in milliseconds ) which the block must be cached
     *
     * @return int
     */
    public function getCacheExpiry(): int;

    /**
     * Returns the cache key based on the context
     *
     * @return string
     */
    public function getCacheSignature(): string;
}
