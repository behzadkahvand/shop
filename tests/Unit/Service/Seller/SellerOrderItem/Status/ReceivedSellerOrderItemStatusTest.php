<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\ReceivedSellerOrderItemStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ReceivedSellerOrderItemStatusTest
 */
final class ReceivedSellerOrderItemStatusTest extends MockeryTestCase
{
    public function testItSupportReceivedSellerOrderItemStatus(): void
    {
        $service = new ReceivedSellerOrderItemStatus(\Mockery::mock(OrderShipmentStatusService::class));

        self::assertTrue($service->support(SellerOrderItemStatus::RECEIVED));
        self::assertFalse($service->support(SellerOrderItemStatus::WAITING));
    }

    public function testItSetSellerOrderItemStatusToReceivedAndDontChangeOrderShipmentStatusToStoraged(): void
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

        $service = new ReceivedSellerOrderItemStatus($orderShipmentStatusService);

        $service->storaged($sellerOrderItem);
    }

    public function testItSetSellerOrderItemStatusToReceivedAndChangeOrderShipmentStatusToStoraged(): void
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

        $service = new ReceivedSellerOrderItemStatus($orderShipmentStatusService);

        $service->storaged($sellerOrderItem);
    }
}
