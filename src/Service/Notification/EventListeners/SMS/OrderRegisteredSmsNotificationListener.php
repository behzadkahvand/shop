<?php

namespace App\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderPaymentMethod;
use App\Events\Order\OrderRegisteredEvent;
use App\Service\Notification\DTOs\Customer\Order\OrderRegisteredSmsNotificationDTO;
use App\Service\Notification\EventListeners\AbstractEditableNotificationListener;
use App\Service\Payment\Events\PaymentSucceeded;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderRegisteredSmsNotificationListener extends AbstractEditableNotificationListener
{
    protected static function getEvents(): array
    {
        return [
            OrderRegisteredEvent::class,
            PaymentSucceeded::class,
        ];
    }

    protected function shouldBail(Event $event): bool
    {
        if ($event instanceof OrderRegisteredEvent) {
            return $event->getOrder()->getPaymentMethod() !== OrderPaymentMethod::OFFLINE;
        }

        return false;
    }

    protected function getDTO(Event $event): OrderRegisteredSmsNotificationDTO
    {
        return new OrderRegisteredSmsNotificationDTO($event->getOrder());
    }
}
