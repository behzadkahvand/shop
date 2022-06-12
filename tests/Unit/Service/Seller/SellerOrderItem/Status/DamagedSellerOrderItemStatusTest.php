<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\DamagedSellerOrderItemStatus;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class DamagedSellerOrderItemStatusTest
 */
final class DamagedSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportDamagedSellerOrderItemStatus(): void
    {
        $service = new DamagedSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::DAMAGED));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }
}
