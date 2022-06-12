<?php

namespace App\Tests\Unit\Service\Product\Availability\Checkers;

use App\Entity\Inventory;
use App\Service\Condition\Exceptions\OutOfStockException;
use App\Service\Condition\OutOfStockCondition as InventoryStockCondition;
use App\Service\Product\Availability\Checkers\InventoryStockProductAvailabilityByInventoryChecker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class InventoryStockProductAvailabilityByInventoryCheckerTest extends MockeryTestCase
{
    public function testItCheckProductShouldBeAvailable(): void
    {
        $inventory = Mockery::mock(Inventory::class);

        $condition = Mockery::mock(InventoryStockCondition::class);
        $condition->shouldReceive(['apply' => null])->once()->with($inventory, 1);

        $checker = new InventoryStockProductAvailabilityByInventoryChecker($condition);

        self::assertTrue($checker->productShouldBeAvailable($inventory));

        $condition->shouldReceive('apply')->once()->with($inventory, 1)->andThrow(OutOfStockException::class);

        self::assertFalse($checker->productShouldBeAvailable($inventory));
    }

    public function testItCheckProductShouldBeUnavailable(): void
    {
        $inventory = Mockery::mock(Inventory::class);

        $condition = Mockery::mock(InventoryStockCondition::class);
        $condition->shouldReceive(['apply' => null])->once()->with($inventory, 1);

        $checker = new InventoryStockProductAvailabilityByInventoryChecker($condition);

        self::assertFalse($checker->productShouldBeUnavailable($inventory));

        $condition->shouldReceive('apply')->once()->with($inventory, 1)->andThrow(OutOfStockException::class);

        self::assertTrue($checker->productShouldBeUnavailable($inventory));
    }
}
