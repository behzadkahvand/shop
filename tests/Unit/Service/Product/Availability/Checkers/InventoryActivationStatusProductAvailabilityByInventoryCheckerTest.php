<?php

namespace App\Tests\Unit\Service\Product\Availability\Checkers;

use App\Entity\Inventory;
use App\Service\Condition\Exceptions\InventoryIsNotActiveException;
use App\Service\Condition\InventoryIsActiveCondition;
use App\Service\Product\Availability\Checkers\InventoryActivationStatusProductAvailabilityByInventoryChecker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class InventoryActivationStatusProductAvailabilityByInventoryCheckerTest extends MockeryTestCase
{
    public function testItCheckProductShouldBeAvailable(): void
    {
        $inventory = Mockery::mock(Inventory::class);

        $condition = Mockery::mock(InventoryIsActiveCondition::class);
        $condition->shouldReceive(['apply' => null])->once()->with($inventory);

        $checker = new InventoryActivationStatusProductAvailabilityByInventoryChecker($condition);

        self::assertTrue($checker->productShouldBeAvailable($inventory));

        $condition->shouldReceive('apply')->once()->with($inventory)->andThrow(InventoryIsNotActiveException::class);

        self::assertFalse($checker->productShouldBeAvailable($inventory));
    }

    public function testItCheckProductShouldBeUnavailable(): void
    {
        $inventory = Mockery::mock(Inventory::class);

        $condition = Mockery::mock(InventoryIsActiveCondition::class);
        $condition->shouldReceive(['apply' => null])->once()->with($inventory);

        $checker = new InventoryActivationStatusProductAvailabilityByInventoryChecker($condition);

        self::assertFalse($checker->productShouldBeUnavailable($inventory));

        $condition->shouldReceive('apply')->once()->with($inventory)->andThrow(InventoryIsNotActiveException::class);

        self::assertTrue($checker->productShouldBeUnavailable($inventory));
    }
}
