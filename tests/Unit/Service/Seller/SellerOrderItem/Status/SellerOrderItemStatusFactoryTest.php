<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException;
use App\Service\Seller\SellerOrderItem\Status\AbstractSellerOrderItemStatus;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SellerOrderItemStatusFactoryTest
 */
final class SellerOrderItemStatusFactoryTest extends MockeryTestCase
{
    public function testItThrowExceptionIfNoneOfStatusObjectsSupportGivenStatus(): void
    {
        $this->expectException(InvalidSellerOrderStatusException::class);

        $factory = new SellerOrderItemStatusFactory([]);
        $factory->create(SellerOrderItemStatus::WAITING);
    }

    public function testItCreateSellerOrderItemStatusObject(): void
    {
        $orderShipmentStatusService = \Mockery::mock(OrderShipmentStatusService::class);

        $status = new class ($orderShipmentStatusService) extends AbstractSellerOrderItemStatus {
            protected function getName(): string
            {
                return SellerOrderItemStatus::WAITING;
            }
        };

        $factory = new SellerOrderItemStatusFactory([$status]);

        self::assertSame($status, $factory->create(SellerOrderItemStatus::WAITING));
    }
}
