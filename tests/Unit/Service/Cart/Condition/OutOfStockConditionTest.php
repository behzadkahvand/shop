<?php

namespace App\Tests\Unit\Service\Cart\Condition;

use App\Entity\Inventory;
use App\Service\Cart\Condition\OutOfStockCondition;
use App\Service\Condition\Exceptions\OutOfStockException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use App\Service\Condition\OutOfStockCondition as BaseOutOfStockCondition;

class OutOfStockConditionTest extends MockeryTestCase
{
    private Inventory $inventory;

    protected function setUp(): void
    {
        $this->inventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setSellerStock(10)
            ->setLeadTime(2);
    }

    protected function tearDown(): void
    {
        unset($this->inventory);
    }

    public function testItThrowAnExceptionWhenQuantityIsGreaterThanAvailableStock(): void
    {
        $this->expectException(OutOfStockException::class);

        (new OutOfStockCondition(new BaseOutOfStockCondition()))->apply($this->inventory, 999);
    }
}
