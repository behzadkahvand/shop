<?php

namespace App\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderStatus;
use App\Service\Notification\DTOs\Customer\Order\OrderCanceledSmsNotificationDTO;
use App\Service\Notification\EventListeners\AbstractEditableNotificationListener;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderCanceledSmsNotificationListener extends AbstractEditableNotificationListener
{
    protected static function getEvents(): array
    {
        return [
            OrderStatusChanged::class,
        ];
    }

    protected function shouldBail(Event $event): bool
    {
        return ! in_array($event->getNewStatus(), [OrderStatus::CANCELED, OrderStatus::CANCELED_SYSTEM], true);
    }

    protected function getDTO(Event $event): OrderCanceledSmsNotificationDTO
    {
        return new OrderCanceledSmsNotificationDTO($event->getOrder());
    }
}
