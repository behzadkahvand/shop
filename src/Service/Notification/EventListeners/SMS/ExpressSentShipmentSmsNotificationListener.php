<?php

namespace App\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderShipmentStatus;
use App\Service\Notification\DTOs\Customer\Shipping\ExpressSentShipmentSmsNotificationDTO;
use App\Service\Notification\EventListeners\AbstractEditableNotificationListener;
use App\Service\OrderShipment\OrderShipmentStatus\Events\OrderShipmentStatusChanged;
use Symfony\Contracts\EventDispatcher\Event;

final class ExpressSentShipmentSmsNotificationListener extends AbstractEditableNotificationListener
{
    protected static function getEvents(): array
    {
        return [
            OrderShipmentStatusChanged::class,
        ];
    }

    protected function shouldBail(Event $event): bool
    {
        if ($event->getNewStatus() !== OrderShipmentStatus::SENT) {
            return true;
        }

        return ! $event->getOrderShipment()->getOrder()->getOrderAddress()->getCity()->isExpress();
    }

    protected function getDTO(Event $event): ExpressSentShipmentSmsNotificationDTO
    {
        return new ExpressSentShipmentSmsNotificationDTO($event->getOrderShipment()->getOrder());
    }
}
