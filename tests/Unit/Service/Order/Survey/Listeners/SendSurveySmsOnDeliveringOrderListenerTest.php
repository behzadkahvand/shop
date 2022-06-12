<?php

namespace App\Tests\Unit\Service\Order\Survey\Listeners;

use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use App\Service\Order\Survey\Listeners\SendSurveySmsOnDeliveringOrderListener;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class SendSurveySmsOnDeliveringOrderListenerTest extends BaseUnitTestCase
{
    public function testItDoNothingIfOrderStatusIsNotDelivered(): void
    {
        $messenger = Mockery::mock(MessageBusInterface::class);
        $messenger->shouldNotReceive('dispatch');

        $event = new OrderStatusChanged(
            Mockery::mock(Order::class),
            OrderStatus::WAITING_FOR_PAY,
            OrderStatus::WAIT_CUSTOMER
        );

        $listener = new SendSurveySmsOnDeliveringOrderListener($messenger);
        $listener->onOrderStatusChanged($event);
    }

    public function testItSendOrderSurveySms(): void
    {
        $messenger = Mockery::mock(MessageBusInterface::class);
        $messenger->shouldReceive('dispatch')
                  ->once()
                  ->with(Mockery::type(AsyncMessage::class))
                  ->andReturnUsing(fn($message) => Envelope::wrap($message));

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);

        $event = new OrderStatusChanged(
            $order,
            OrderStatus::CONFIRMED,
            OrderStatus::DELIVERED
        );

        $listener = new SendSurveySmsOnDeliveringOrderListener($messenger);
        $listener->onOrderStatusChanged($event);
    }
}
