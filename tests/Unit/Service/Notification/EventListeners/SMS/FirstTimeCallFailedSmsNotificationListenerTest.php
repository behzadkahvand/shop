<?php

namespace App\Tests\Unit\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Entity\OrderStatusLog;
use App\Service\Notification\EventListeners\SMS\FirstTimeCallFailedSmsNotificationListener;
use App\Service\Notification\NotificationService;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class FirstTimeCallFailedSmsNotificationListenerTest extends MockeryTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = FirstTimeCallFailedSmsNotificationListener::getSubscribedEvents();

        self::assertEquals([OrderStatusChanged::class => '__invoke'], $events);
    }

    public function testItBailsIfNewStatusIsNotFirstTimeCalled(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldNotReceive('send');

        $listener = new FirstTimeCallFailedSmsNotificationListener(
            $notificationService
        );

        $listener(new OrderStatusChanged(
            Mockery::mock(Order::class),
            OrderStatus::NEW,
            OrderStatus::CANCELED
        ));
    }

    public function testItBailsIfItsNotTheFirstTimeCallingCustomerFailed(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldNotReceive('send');

        $orderStatusLog1 = Mockery::mock(OrderStatusLog::class);
        $orderStatusLog1->shouldReceive('getStatusTo')->once()->withNoArgs()->andReturn(OrderStatus::CALL_FAILED);

        $orderStatusLog2 = Mockery::mock(OrderStatusLog::class);
        $orderStatusLog2->shouldReceive('getStatusTo')->once()->withNoArgs()->andReturn(OrderStatus::CALL_FAILED);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getOrderStatusLogs')
            ->once()
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$orderStatusLog1, $orderStatusLog2]));

        $listener = new FirstTimeCallFailedSmsNotificationListener(
            $notificationService
        );

        $listener(new OrderStatusChanged(
            $order,
            OrderStatus::CONFIRMED,
            OrderStatus::CALL_FAILED
        ));
    }
}
