<?php

namespace App\Service\Notification\EventListeners\SMS;

use App\Service\Notification\DTOs\Customer\Payment\FailedPaymentSmsNotificationDTO;
use App\Service\Notification\EventListeners\AbstractEditableNotificationListener;
use App\Service\Payment\Events\PaymentFailed;
use Symfony\Contracts\EventDispatcher\Event;

final class FailedPaymentSmsNotificationListener extends AbstractEditableNotificationListener
{
    protected static function getEvents(): array
    {
        return [
            PaymentFailed::class,
        ];
    }

    protected function shouldBail(Event $event): bool
    {
        return false;
    }

    protected function getDTO(Event $event): FailedPaymentSmsNotificationDTO
    {
        return new FailedPaymentSmsNotificationDTO($event->getOrder());
    }
}
