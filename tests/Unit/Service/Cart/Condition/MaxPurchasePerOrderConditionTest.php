<?php

namespace App\Tests\Unit\Service\Cart\Condition;

use App\Entity\Inventory;
use App\Service\Cart\Condition\MaxPurchasePerOrderCondition;
use App\Service\Condition\Exceptions\MaxPurchasePerOrderExceededException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use App\Service\Condition\MaxPurchasePerOrderCondition as BaseMaxPurchasePerOrderCondition;

class MaxPurchasePerOrderConditionTest extends MockeryTestCase
{
    private Inventory $inventory;

    protected function setUp(): void
    {
        $this->inventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setMaxPurchasePerOrder(10)
            ->setLeadTime(2);
    }

    protected function tearDown(): void
    {
        unset($this->inventory);
    }

    public function testItThrowAnExceptionWhenQuantityIsNegative(): void
    {
        $this->expectException(MaxPurchasePerOrderExceededException::class);

        (new MaxPurchasePerOrderCondition(new BaseMaxPurchasePerOrderCondition()))->apply($this->inventory, -1);
    }

    public function testItThrowAnExceptionWhenQuantityIsGreaterThanMaxPurchasePerOrder(): void
    {
        $this->expectException(MaxPurchasePerOrderExceededException::class);

        (new MaxPurchasePerOrderCondition(new BaseMaxPurchasePerOrderCondition()))->apply($this->inventory, 999);
    }
}
