<?php

namespace App\Tests\Unit\Service\Cart\Condition;

use App\Dictionary\InventoryStatus;
use App\Entity\Inventory;
use App\Service\Cart\Condition\InventoryIsConfirmedCondition;
use App\Service\Condition\Exceptions\InventoryIsNotConfirmedException;
use App\Service\Condition\InventoryIsConfirmedCondition as BaseInventoryIsConfirmedCondition;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InventoryIsConfirmedConditionTest extends MockeryTestCase
{
    private Inventory $inventory;

    protected function setUp(): void
    {
        $this->inventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setSellerStock(10)
            ->setLeadTime(2)
            ->setIsActive(true)
            ->setStatus(InventoryStatus::WAIT_FOR_CONFIRM);
    }

    protected function tearDown(): void
    {
        unset($this->inventory);
    }

    public function testItThrowAnExceptionWhenInventoryIsNotConfirmed(): void
    {
        $this->expectException(InventoryIsNotConfirmedException::class);

        (new InventoryIsConfirmedCondition(new BaseInventoryIsConfirmedCondition()))->apply($this->inventory, 1);
    }
}
