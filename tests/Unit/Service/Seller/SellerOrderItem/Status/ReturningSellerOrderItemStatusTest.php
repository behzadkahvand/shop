<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\ReturningSellerOrderItemStatus;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ReturningSellerOrderItemStatusTest
 */
final class ReturningSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportReturningSellerOrderItemStatus(): void
    {
        $service = new ReturningSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::RETURNING));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }
}
