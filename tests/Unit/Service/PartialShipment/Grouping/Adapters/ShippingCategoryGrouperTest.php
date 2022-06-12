<?php

namespace App\Tests\Unit\Service\PartialShipment\Grouping\Adapters;

use App\Service\PartialShipment\Grouping\Adapters\ShippingCategoryGrouper;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ShippingCategoryGrouperTest
 */
final class ShippingCategoryGrouperTest extends MockeryTestCase
{
    public function testItGroupShipmentItemsByShippingCategoryId()
    {
        $item1 = \Mockery::mock(PartialShipmentItem::class);
        $item1->shouldReceive('getShippingCategoryId')->once()->withNoArgs()->andReturn(1);
        $item2 = \Mockery::mock(PartialShipmentItem::class);
        $item2->shouldReceive('getShippingCategoryId')->once()->withNoArgs()->andReturn(2);
        $grouper = new ShippingCategoryGrouper();

        self::assertEquals([1 => [$item1], 2 => [$item2]], $grouper->group([$item1, $item2]));
    }
}
