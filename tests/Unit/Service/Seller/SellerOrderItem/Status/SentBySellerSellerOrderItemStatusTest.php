<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\SentBySellerSellerOrderItemStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SentBySellerSellerOrderItemStatusTest
 */
final class SentBySellerSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportSentBySellerSellerOrderItemStatus(): void
    {
        $service = new SentBySellerSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::SENT_BY_SELLER));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }

    public function testItSetSellerOrderItemStatusToStoragedAndDontChangeOrderShipmentStatusToStoraged(): void
    {
        $sellerOrderItem = \Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('setStatus')->once()->with(SellerOrderItemStatus::STORAGED)->andReturnSelf();
        $sellerOrderItem->shouldReceive('getOrderItem->getOrderShipment')
                        ->once()
                        ->withNoArgs()
                        ->andReturnUsing(function () {
                            $shipment = \Mockery::mock(OrderShipment::class);
                            $shipment->shouldReceive('getOrderItems')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturn(new ArrayCollection([]));

                            return $shipment;
                        });

        $orderShipmentStatusService = \Mockery::mock(OrderShipmentStatusService::class);
        $orderShipmentStatusService->shouldNotReceive('change');

        $service = new SentBySellerSellerOrderItemStatus($orderShipmentStatusService);

        $service->storaged($sellerOrderItem);
    }

    public function testItSetSellerOrderItemStatusAndOrderShipmentStatusToStoraged(): void
    {
        $shipment        = \Mockery::mock(OrderShipment::class);
        $orderItem       = \Mockery::mock(OrderItem::class);
        $sellerOrderItem = \Mockery::mock(SellerOrderItem::class);

        $orderItem->shouldReceive('getOrderShipment')->once()->withNoArgs()->andReturn($shipment);

        $shipment->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn(new ArrayCollection([$orderItem]));

        $sellerOrderItem->shouldReceive('setStatus')->once()->with(SellerOrderItemStatus::STORAGED)->andReturnSelf();
        $sellerOrderItem->shouldReceive('isRejected')->once()->withNoArgs()->andReturnFalse();
        $sellerOrderItem->shouldReceive('isStoraged')->once()->withNoArgs()->andReturnTrue();
        $sellerOrderItem->shouldReceive('getOrderItem')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($orderItem);

        $orderItem->shouldReceive('getSellerOrderItem')->twice()->withNoArgs()->andReturn($sellerOrderItem);

        $orderShipmentStatusService = \Mockery::mock(OrderShipmentStatusService::class);
        $orderShipmentStatusService->shouldReceive('change')
                                   ->once()
                                   ->with($shipment, OrderShipmentStatus::WAREHOUSE)
                                   ->andReturn();

        $service = new SentBySellerSellerOrderItemStatus($orderShipmentStatusService);

        $service->storaged($sellerOrderItem);
    }
}
