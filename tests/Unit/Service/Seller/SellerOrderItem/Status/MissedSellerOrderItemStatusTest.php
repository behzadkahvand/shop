<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\MissedSellerOrderItemStatus;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class MissedSellerOrderItemStatusTest
 */
final class MissedSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportMissedSellerOrderItemStatus(): void
    {
        $service = new MissedSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::MISSED));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }
}
