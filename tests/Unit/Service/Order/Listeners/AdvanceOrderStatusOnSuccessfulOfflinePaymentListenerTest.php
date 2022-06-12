<?php

namespace App\Tests\Unit\Service\Order\Listeners;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Entity\Transaction;
use App\Service\Order\Listeners\AdvanceOrderStatusOnSuccessfulOfflinePaymentListener;
use App\Service\Order\Listeners\AdvanceOrderStatusOnSuccessfulOnlinePaymentListener;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Payment\Events\PaymentSucceeded;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

final class AdvanceOrderStatusOnSuccessfulOfflinePaymentListenerTest extends MockeryTestCase
{
    public function testGettingSubscribedEvents(): void
    {
        self::assertEquals([
            PaymentSucceeded::class => ['onPaymentSucceeded', 1000],
        ], AdvanceOrderStatusOnSuccessfulOnlinePaymentListener::getSubscribedEvents());
    }

    public function testItCanChangeOrderStatusToDelivered(): void
    {
        $order = m::mock(Order::class);
        $transaction = m::mock(Transaction::class);
        $orderShipmentStatusService = m::mock(OrderShipmentStatusService::class);

        $transaction->shouldReceive('getOrderShipment')
            ->once()
            ->withNoArgs()
            ->andReturn(m::mock(OrderShipment::class));

        $order->shouldReceive(['getPaymentMethod' => OrderPaymentMethod::OFFLINE])
            ->once()
            ->withNoArgs();

        $orderShipmentStatusService->shouldReceive('change')
            ->once()
            ->with(m::type(OrderShipment::class), OrderShipmentStatus::DELIVERED)
            ->andReturn();

        $order->shouldReceive(['getStatus' => OrderStatus::DELIVERED])
            ->once()
            ->withNoArgs();

        $order->shouldReceive('setPaidAt')
            ->once()
            ->with(m::type(DateTime::class));

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('commit')->once()->withNoArgs()->andReturn();

        $listener = new AdvanceOrderStatusOnSuccessfulOfflinePaymentListener(
            $manager,
            $orderShipmentStatusService,
        );

        $listener->onPaymentSucceeded(new PaymentSucceeded($order, $transaction));
    }

    public function testItCanRollbackTransactionOnException(): void
    {
        $order = m::mock(Order::class);
        $transaction = m::mock(Transaction::class);
        $orderShipmentStatusService = m::mock(OrderShipmentStatusService::class);

        $transaction->shouldReceive('getOrderShipment')
            ->once()
            ->withNoArgs()
            ->andReturn(m::mock(OrderShipment::class));

        $order->shouldReceive(['getPaymentMethod' => OrderPaymentMethod::OFFLINE])
            ->once()
            ->withNoArgs();

        $orderShipmentStatusService->shouldReceive('change')
            ->once()
            ->with(m::type(OrderShipment::class), OrderShipmentStatus::DELIVERED)
            ->andThrow(Exception::class);

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();

        $listener = new AdvanceOrderStatusOnSuccessfulOfflinePaymentListener(
            $manager,
            $orderShipmentStatusService,
        );

        $this->expectException(Exception::class);

        $listener->onPaymentSucceeded(new PaymentSucceeded($order, $transaction));
    }
}
