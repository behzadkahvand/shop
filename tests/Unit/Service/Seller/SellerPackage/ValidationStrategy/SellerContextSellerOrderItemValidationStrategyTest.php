<?php

namespace App\Tests\Unit\Service\Seller\SellerPackage\ValidationStrategy;

use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\SellerOrderItem;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException;
use App\Service\Seller\SellerOrderItem\Exceptions\SellerOrderItemCanNotBePackagedException;
use App\Service\Seller\SellerPackage\ValidationStrategy\SellerContextSellerOrderItemValidationStrategy;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SellerContextSellerOrderItemValidationStrategyTest
 */
final class SellerContextSellerOrderItemValidationStrategyTest extends MockeryTestCase
{
    public function testItThrowExceptionIfOrderStatusIsNotConfirmed(): void
    {
        $order = \Mockery::mock(Order::class);
        $order->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(OrderStatus::WAITING_FOR_PAY);

        $orderItem = \Mockery::mock(OrderItem::class);
        $orderItem->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);

        $sellerOrderItem = \Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('getOrderItem')->once()->withNoArgs()->andReturn($orderItem);

        $strategy = new SellerContextSellerOrderItemValidationStrategy();

        $this->expectException(InvalidSellerOrderStatusException::class);

        $strategy->validate([$sellerOrderItem]);
    }

    public function testItThrowExceptionIfSellerOrderItemIsNotWaitingForSend(): void
    {
        $order = \Mockery::mock(Order::class);
        $order->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(OrderStatus::CONFIRMED);

        $orderItem = \Mockery::mock(OrderItem::class);
        $orderItem->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);

        $sellerOrderItem = \Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('getOrderItem')->once()->withNoArgs()->andReturn($orderItem);
        $sellerOrderItem->shouldReceive('isWaitingForSend')->once()->withNoArgs()->andReturnFalse();

        $strategy = new SellerContextSellerOrderItemValidationStrategy();

        $this->expectException(SellerOrderItemCanNotBePackagedException::class);

        $strategy->validate([$sellerOrderItem]);
    }
}
