<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\SellerOrderItem;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\CanceledByUserSellerOrderItemStatus;
use App\Service\Seller\SellerOrderItem\Status\Exceptions\InvalidSellerOrderItemStatusTransitionException;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class CanceledByUserSellerOrderItemStatusTest
 */
final class CanceledByUserSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportCanceledByUserSellerOrderItemStatus(): void
    {
        $service = new CanceledByUserSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::CANCELED_BY_USER));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }

    /**
     * @dataProvider methodProvider
     *
     * @param string $method
     */
    /*public function testItThrowExceptionIfInvalidTransitionIsInvoked(string $method): void
    {
        $service = new CanceledByUserSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        $currentStatus = array_rand(SellerOrderItemStatus::toArray());

        $sellerOrderItem = \Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($currentStatus);

        $this->expectException(InvalidSellerOrderItemStatusTransitionException::class);
        $this->expectExceptionMessage(sprintf(
            "Seller order item status transition from %s to %s is invalid!",
            $currentStatus,
            strtoupper(snake_case($method))
        ));

        $service->$method($sellerOrderItem);
    }

    public function methodProvider()
    {
        return array_map(
            static fn(string $method) => [$method],
            array_diff(get_class_methods(CanceledByUserSellerOrderItemStatus::class), ['__construct', 'support'])
        );
    }*/
}
