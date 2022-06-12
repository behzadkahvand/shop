<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\WaitingSellerOrderItemStatus;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class WaitingSellerOrderItemStatusTest
 */
final class WaitingSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportWaitingSellerOrderItemStatus(): void
    {
        $service = new WaitingSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::WAITING));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING_FOR_SEND));
    }
}
