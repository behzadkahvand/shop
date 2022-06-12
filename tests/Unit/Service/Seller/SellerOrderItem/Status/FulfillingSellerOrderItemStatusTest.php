<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\FulfillingSellerOrderItemStatus;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class FulfillingSellerOrderItemStatusTest
 */
final class FulfillingSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportFulfillingSellerOrderItemStatus(): void
    {
        $service = new FulfillingSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::FULFILLING));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }
}
