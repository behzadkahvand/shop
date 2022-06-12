<?php

namespace App\Tests\Unit\Service\Cart\Condition;

use App\Entity\Inventory;
use App\Service\Cart\Condition\InventoryIsActiveCondition;
use App\Service\Condition\Exceptions\InventoryIsNotActiveException;
use App\Service\Condition\InventoryIsActiveCondition as BaseInventoryIsActiveCondition;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InventoryIsActiveConditionTest extends MockeryTestCase
{
    private Inventory $inventory;

    protected function setUp(): void
    {
        $this->inventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setSellerStock(10)
            ->setLeadTime(2)
            ->setIsActive(false);
    }

    protected function tearDown(): void
    {
        unset($this->inventory);
    }

    public function testItThrowAnExceptionWhenInventoryIsNotActive(): void
    {
        $this->expectException(InventoryIsNotActiveException::class);

        (new InventoryIsActiveCondition(new BaseInventoryIsActiveCondition()))->apply($this->inventory, 1);
    }
}
