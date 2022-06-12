<?php

namespace App\Tests\Unit\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\Order;
use App\Entity\Transaction;
use App\Events\Order\OrderRegisteredEvent;
use App\Service\Notification\DTOs\Customer\Order\OrderRegisteredSmsNotificationDTO;
use App\Service\Notification\EventListeners\SMS\OrderRegisteredSmsNotificationListener;
use App\Service\Notification\NotificationService;
use App\Service\Payment\Events\PaymentSucceeded;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class OrderRegisteredSmsNotificationListenerTest extends MockeryTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = OrderRegisteredSmsNotificationListener::getSubscribedEvents();

        self::assertEquals([
            OrderRegisteredEvent::class => '__invoke',
            PaymentSucceeded::class     => '__invoke',
        ], $events);
    }

    public function testItBailOnOnlineOrders(): void
    {
        $order = \Mockery::mock(Order::class);
        $order->shouldReceive(['getPaymentMethod' => OrderPaymentMethod::ONLINE]);

        $notificationService = \Mockery::spy(NotificationService::class);
        $listener            = new OrderRegisteredSmsNotificationListener($notificationService);

        $listener(new OrderRegisteredEvent($order));

        $notificationService->shouldNotHaveReceived('send');
    }

    public function testItDontBailOnOfflineOrders(): void
    {
        $order = \Mockery::mock(Order::class);
        $order->shouldReceive(['getPaymentMethod' => OrderPaymentMethod::OFFLINE]);

        $notificationService = \Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('send')
                            ->once()
                            ->with(\Mockery::type(OrderRegisteredSmsNotificationDTO::class))
                            ->andReturn();

        $listener = new OrderRegisteredSmsNotificationListener($notificationService);

        $listener(new OrderRegisteredEvent($order));
    }

    public function testItDontBailOnSuccessfulPayments(): void
    {
        $order = \Mockery::mock(Order::class);
        $order->shouldReceive(['getPaymentMethod' => OrderPaymentMethod::ONLINE]);

        $notificationService = \Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('send')
                            ->once()
                            ->with(\Mockery::type(OrderRegisteredSmsNotificationDTO::class))
                            ->andReturn();

        $listener = new OrderRegisteredSmsNotificationListener($notificationService);

        $listener(new PaymentSucceeded($order, \Mockery::mock(Transaction::class)));
    }
}
