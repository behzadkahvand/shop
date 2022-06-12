<?php

namespace App\Tests\Unit\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;
use App\Service\Notification\EventListeners\SMS\WaitingForSendShipmentSmsNotificationListener;
use App\Service\Notification\NotificationService;
use App\Service\OrderShipment\OrderShipmentStatus\Events\OrderShipmentStatusChanged;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class WaitingForSendShipmentSmsNotificationTest extends MockeryTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = WaitingForSendShipmentSmsNotificationListener::getSubscribedEvents();

        self::assertEquals([OrderShipmentStatusChanged::class => '__invoke'], $events);
    }

    public function testItBailsIfNewStatusIsNotWaitingForSend(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldNotReceive('send');

        $listener = new WaitingForSendShipmentSmsNotificationListener($notificationService);

        $listener(new OrderShipmentStatusChanged(
            Mockery::mock(OrderShipment::class),
            OrderShipmentStatus::NEW,
            OrderShipmentStatus::AFTER_SALES
        ));
    }
}
