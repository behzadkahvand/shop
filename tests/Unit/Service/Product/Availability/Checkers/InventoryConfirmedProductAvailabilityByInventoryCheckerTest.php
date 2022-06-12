<?php

namespace App\Tests\Unit\Service\Product\Availability\Checkers;

use App\Entity\Inventory;
use App\Service\Condition\InventoryIsConfirmedCondition;
use App\Service\Product\Availability\Checkers\InventoryConfirmedProductAvailabilityByInventoryChecker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InventoryConfirmedProductAvailabilityByInventoryCheckerTest extends MockeryTestCase
{
    public function testItCheckProductShouldBeAvailable(): void
    {
        $checker = new InventoryConfirmedProductAvailabilityByInventoryChecker(new InventoryIsConfirmedCondition());

        $inventory = Mockery::mock(Inventory::class);

        $inventory->shouldReceive(['isConfirmed' => true])->once()->withNoArgs();
        self::assertTrue($checker->productShouldBeAvailable($inventory));

        $inventory->shouldReceive(['isConfirmed' => false])->once()->withNoArgs();
        self::assertFalse($checker->productShouldBeAvailable($inventory));
    }

    public function testItCheckProductShouldBeUnavailable(): void
    {
        $checker = new InventoryConfirmedProductAvailabilityByInventoryChecker(new InventoryIsConfirmedCondition());

        $inventory = Mockery::mock(Inventory::class);

        $inventory->shouldReceive(['isConfirmed' => true])->once()->withNoArgs();
        self::assertFalse($checker->productShouldBeUnavailable($inventory));

        $inventory->shouldReceive(['isConfirmed' => false])->once()->withNoArgs();
        self::assertTrue($checker->productShouldBeUnavailable($inventory));
    }
}
