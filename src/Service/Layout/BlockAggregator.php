<?php

namespace App\Service\Layout;

use App\Service\Layout\Block\BlockInterface;

class BlockAggregator
{
    /**
     * @var BlockInterface[]
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
