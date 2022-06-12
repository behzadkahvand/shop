<?php

namespace App\Tests\Unit\Service\Layout;

use App\Service\Layout\OnSaleBlock\OnSaleInventoryBlock;
use App\Service\Layout\OnSaleBlock\OnSaleProductBlock;
use App\Service\Layout\OnSaleBlockAggregator;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class OnSaleBlockAggregatorTest extends BaseUnitTestCase
{
    private Mockery\LegacyMockInterface|OnSaleProductBlock|Mockery\MockInterface|null $onSaleProductBlockMock;
    private Mockery\LegacyMockInterface|OnSaleInventoryBlock|Mockery\MockInterface|null $onSaleInventoryBlockMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->onSaleProductBlockMock = Mockery::mock(OnSaleProductBlock::class);
        $this->onSaleInventoryBlockMock = Mockery::mock(OnSaleInventoryBlock::class);
    }

    public function testItGeneratesBlocks(): void
    {
        $context = [
            'serialization_groups' => ['customer.layout.onSaleBlocks', 'media'],
        ];
        $this->onSaleProductBlockMock->shouldReceive('getCode')->once()->withNoArgs()->andReturn('onSaleProducts');
        $this->onSaleProductBlockMock->shouldReceive('generate')->once()->with($context)->andReturn([]);

        $this->onSaleInventoryBlockMock->shouldReceive('getCode')->once()->withNoArgs()->andReturn('onSaleInventories');
        $this->onSaleInventoryBlockMock->shouldReceive('generate')->once()->with($context)->andReturn([]);

        $blockAggregator = new OnSaleBlockAggregator([$this->onSaleInventoryBlockMock, $this->onSaleProductBlockMock]);
        $return = $blockAggregator->generate($context);
        self::assertArrayHasKey('onSaleInventories', $return);
        self::assertArrayHasKey('onSaleProducts', $return);
        self::assertEmpty($return['onSaleProducts']);
        self::assertEmpty($return['onSaleInventories']);
    }
}
