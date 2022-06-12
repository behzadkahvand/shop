<?php

namespace App\Tests\Unit\Service\Notification\EventListeners\SMS;

use App\Service\Notification\EventListeners\SMS\FailedPaymentSmsNotificationListener;
use App\Service\Payment\Events\PaymentFailed;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class FailedPaymentSmsNotificationListenerTest extends MockeryTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = FailedPaymentSmsNotificationListener::getSubscribedEvents();

        self::assertEquals([PaymentFailed::class => '__invoke'], $events);
    }
}
