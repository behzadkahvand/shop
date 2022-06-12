<?php

namespace App\Service\Layout;

interface LayoutInterface
{
    /**
     * Returns the block code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Generates the block data
     *
     * @param array $context
     * @return array
     */
    public function generate(array $context = []): array;
}
