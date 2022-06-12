<?php

namespace App\Tests\Unit\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;
use App\Service\Notification\EventListeners\SMS\ExpressSentShipmentSmsNotificationListener;
use App\Service\Notification\NotificationService;
use App\Service\OrderShipment\OrderShipmentStatus\Events\OrderShipmentStatusChanged;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class ExpressSentShipmentSmsNotificationTest extends MockeryTestCase
{
    public function testItCanGetSubscribedEvents(): void
    {
        $events = ExpressSentShipmentSmsNotificationListener::getSubscribedEvents();

        self::assertEquals([OrderShipmentStatusChanged::class => '__invoke'], $events);
    }

    public function testItBailsIfNewStatusIsNotSent(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldNotReceive('send');

        $listener = new ExpressSentShipmentSmsNotificationListener($notificationService);
        $listener(
            new OrderShipmentStatusChanged(
                Mockery::mock(OrderShipment::class),
                OrderShipmentStatus::NEW,
                OrderShipmentStatus::AFTER_SALES
            )
        );
    }

    public function testItBailsIfShippingMethodIsNotExpress(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldNotReceive('send');

        $orderShipment = Mockery::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getOrder->getOrderAddress->getCity->isExpress')
            ->once()
            ->withNoArgs()
            ->andReturnFalse();

        $listener = new ExpressSentShipmentSmsNotificationListener($notificationService);
        $listener(
            new OrderShipmentStatusChanged(
                $orderShipment,
                OrderShipmentStatus::NEW,
                OrderShipmentStatus::SENT
            )
        );
    }
}
