<?php

namespace App\Tests\Unit\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Service\Notification\EventListeners\SMS\OrderCanceledSmsNotificationListener;
use App\Service\Notification\NotificationService;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class OrderCanceledSmsNotificationListenerTest extends MockeryTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = OrderCanceledSmsNotificationListener::getSubscribedEvents();

        self::assertEquals([OrderStatusChanged::class => '__invoke'], $events);
    }

    public function testItBailsIfNewOrderStatusIsNotCanceled(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldNotReceive('send');

        $listener = new OrderCanceledSmsNotificationListener($notificationService);

        $listener(new OrderStatusChanged(
            Mockery::mock(Order::class),
            OrderStatus::NEW,
            OrderStatus::WAIT_CUSTOMER
        ));
    }
}
