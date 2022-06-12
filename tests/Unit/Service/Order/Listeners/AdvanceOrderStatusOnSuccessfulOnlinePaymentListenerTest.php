<?php

namespace App\Tests\Unit\Service\Order\Listeners;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Entity\OrderAffiliator;
use App\Entity\Transaction;
use App\Messaging\Messages\Command\Order\SendOrderAffiliatorPurchaseRequest;
use App\Service\Order\AutoConfirm\AutoConfirmOrderServiceInterface;
use App\Service\Order\Listeners\AdvanceOrderStatusOnSuccessfulOnlinePaymentListener;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use App\Service\Payment\Events\PaymentSucceeded;
use App\Tests\Unit\BaseUnitTestCase;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class AdvanceOrderStatusOnSuccessfulOnlinePaymentListenerTest extends BaseUnitTestCase
{
    public function testGettingSubscribedEvents(): void
    {
        self::assertEquals([
            PaymentSucceeded::class => ['onPaymentSucceeded', 1000],
        ], AdvanceOrderStatusOnSuccessfulOnlinePaymentListener::getSubscribedEvents());
    }

    public function testItAdvanceOrderStatusToWaitCustomer(): void
    {
        $order           = Mockery::mock(Order::class);
        $orderAffiliator = Mockery::mock(OrderAffiliator::class);

        $order->shouldReceive(['getPaymentMethod' => OrderPaymentMethod::ONLINE])
              ->once()
              ->withNoArgs();

        $order->shouldReceive(['getStatus' => OrderStatus::WAITING_FOR_PAY])
              ->once()
              ->withNoArgs();

        $order->shouldReceive('getId')->once()->withNoArgs()->andReturn(42);
        $order->shouldReceive('getAffiliator')->once()->withNoArgs()->andReturn($orderAffiliator);

        $order->shouldReceive('setStatus')->once()->with(OrderStatus::WAIT_CUSTOMER)->andReturnSelf();
        $order->shouldReceive('setPaidAt')->once()->with(Mockery::type(DateTime::class))->andReturnSelf();

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('commit')->once()->withNoArgs()->andReturn();

        $orderStatusLogService = Mockery::mock(CreateOrderStatusLogService::class);
        $orderStatusLogService->shouldReceive('perform')
                              ->once()
                              ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false)
                              ->andReturn();

        $autoConfirmOrderService = Mockery::mock(AutoConfirmOrderServiceInterface::class);
        $autoConfirmOrderService->shouldReceive(['isConfirmable' => false])->once()->with($order);

        $messageBus = Mockery::mock(MessageBusInterface::class);
        $messageBus->shouldReceive('dispatch')
                   ->once()
                   ->with(Mockery::type(SendOrderAffiliatorPurchaseRequest::class))
                   ->andReturn(new Envelope(new stdClass()));

        $listener = new AdvanceOrderStatusOnSuccessfulOnlinePaymentListener(
            $em,
            $orderStatusLogService,
            $autoConfirmOrderService,
            $messageBus
        );

        $listener->onPaymentSucceeded(new PaymentSucceeded($order, Mockery::mock(Transaction::class)));
    }

    public function testItAdvanceOrderStatusToConfirmed(): void
    {
        $order = Mockery::mock(Order::class);

        $order->shouldReceive(['getPaymentMethod' => OrderPaymentMethod::ONLINE])
              ->once()
              ->withNoArgs();

        $order->shouldReceive(['getStatus' => OrderStatus::WAITING_FOR_PAY])
              ->once()
              ->withNoArgs();

        $order->shouldReceive('getAffiliator')->once()->withNoArgs()->andReturnNull();

        $order->shouldReceive('setStatus')->once()->with(OrderStatus::WAIT_CUSTOMER)->andReturnSelf();
        $order->shouldReceive('setPaidAt')->once()->with(Mockery::type(DateTime::class))->andReturnSelf();

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('commit')->once()->withNoArgs()->andReturn();

        $orderStatusLogService = Mockery::mock(CreateOrderStatusLogService::class);
        $orderStatusLogService->shouldReceive('perform')
                              ->once()
                              ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false)
                              ->andReturn();

        $autoConfirmOrderService = Mockery::mock(AutoConfirmOrderServiceInterface::class);
        $autoConfirmOrderService->shouldReceive(['isConfirmable' => true])->once()->with($order);
        $autoConfirmOrderService->shouldReceive('confirm')->once()->with($order)->andReturn();

        $messageBus = Mockery::mock(MessageBusInterface::class);

        $listener = new AdvanceOrderStatusOnSuccessfulOnlinePaymentListener(
            $em,
            $orderStatusLogService,
            $autoConfirmOrderService,
            $messageBus
        );

        $listener->onPaymentSucceeded(new PaymentSucceeded($order, Mockery::mock(Transaction::class)));
    }

    public function testItRollbackTransactionOnException(): void
    {
        $order = Mockery::mock(Order::class);

        $order->shouldReceive(['getPaymentMethod' => OrderPaymentMethod::ONLINE])
              ->once()
              ->withNoArgs();

        $order->shouldReceive(['getStatus' => OrderStatus::WAITING_FOR_PAY])
              ->once()
              ->withNoArgs();

        $order->shouldReceive('setStatus')->once()->with(OrderStatus::WAIT_CUSTOMER)->andReturnSelf();
        $order->shouldReceive('setPaidAt')->once()->with(Mockery::type(DateTime::class))->andReturnSelf();

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $orderStatusLogService = Mockery::mock(CreateOrderStatusLogService::class);
        $orderStatusLogService->shouldReceive('perform')
                              ->once()
                              ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false)
                              ->andThrow(Exception::class);

        $listener = new AdvanceOrderStatusOnSuccessfulOnlinePaymentListener(
            $em,
            $orderStatusLogService,
            Mockery::mock(AutoConfirmOrderServiceInterface::class),
            Mockery::mock(MessageBusInterface::class)
        );

        $this->expectException(Exception::class);

        $listener->onPaymentSucceeded(new PaymentSucceeded($order, Mockery::mock(Transaction::class)));
    }
}
