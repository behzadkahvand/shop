<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\StoragedSellerOrderItemStatus;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class StoragedSellerOrderItemStatusTest
 */
final class StoragedSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportStoragedSellerOrderItemStatus(): void
    {
        $service = new StoragedSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::STORAGED));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }
}
