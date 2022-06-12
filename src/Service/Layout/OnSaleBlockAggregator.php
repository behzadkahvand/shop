<?php

namespace App\Service\Layout;

use App\Service\Layout\OnSaleBlock\OnSaleBlockInterface;

class OnSaleBlockAggregator
{
    /**
     * @var OnSaleBlockInterface[]
     */
    private iterable $blocks;

    public function __construct(iterable $blocks)
    {
        $this->blocks = $blocks;
    }

    public function generate(array $context = []): array
    {
        $result = [];
        foreach ($this->blocks as $block) {
            $result[$block->getCode()] = $block->generate($context);
        }

        return $result;
    }
}
