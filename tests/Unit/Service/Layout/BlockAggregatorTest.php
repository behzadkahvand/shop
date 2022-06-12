<?php

namespace App\Tests\Unit\Service\Layout;

use App\Service\Layout\Block\BlockInterface;
use App\Service\Layout\BlockAggregator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class BlockAggregatorTest extends MockeryTestCase
{
    public function testItGeneratesBlocks()
    {
        $categoryBlock = Mockery::mock(BlockInterface::class);
        $categoryBlock->shouldReceive('getCode')->once()->withNoArgs()->andReturn('X');
        $context = ['categories' => ['code']];
        $categoryBlock->shouldReceive('generate')->once()->with($context)->andReturn([]);

        $blockAggregator = new BlockAggregator([$categoryBlock]);
        $returnee = $blockAggregator->generate($context);

        self::assertArrayHasKey('X', $returnee);
    }
}
