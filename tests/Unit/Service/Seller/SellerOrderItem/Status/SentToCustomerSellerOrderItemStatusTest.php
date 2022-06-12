<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\SentToCustomerSellerOrderItemStatus;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SentToCustomerSellerOrderItemStatusTest
 */
final class SentToCustomerSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportSentToCustomerSellerOrderItemStatus(): void
    {
        $service = new SentToCustomerSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::SENT_TO_CUSTOMER));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }
}
