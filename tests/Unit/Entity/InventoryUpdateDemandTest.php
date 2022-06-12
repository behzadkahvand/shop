<?php

namespace App\Tests\Unit\Entity;

use App\Entity\InventoryUpdateDemand;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InventoryUpdateDemandTest extends TestCase
{
    public function validStatuses()
    {
        yield ['PENDING'];
    }

    /**
     * @dataProvider validStatuses
     */
    public function testSetStatusWillUpdateStatusAttribute($status)
    {
        $inventoryUpdateDemand = new InventoryUpdateDemand();
        $inventoryUpdateDemand->setStatus($status);
        self::assertEquals($status, $inventoryUpdateDemand->getStatus());
    }

    public function testSetStatusFailWithInvalidStatus()
    {
        $inventoryUpdateDemand = new InventoryUpdateDemand();
        self::expectException(InvalidArgumentException::class);
        $inventoryUpdateDemand->setStatus('invalid status');
    }
}
