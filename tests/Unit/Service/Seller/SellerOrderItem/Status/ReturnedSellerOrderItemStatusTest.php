<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\ReturnedSellerOrderItemStatus;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ReturnedSellerOrderItemStatusTest
 */
final class ReturnedSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportReturnedSellerOrderItemStatus(): void
    {
        $service = new ReturnedSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::RETURNED));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }
}
