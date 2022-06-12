<?php

namespace App\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderShipmentStatus;
use App\Service\Notification\DTOs\Customer\Shipping\WaitingForSendShipmentSmsNotificationDTO;
use App\Service\Notification\EventListeners\AbstractEditableNotificationListener;
use App\Service\OrderShipment\OrderShipmentStatus\Events\OrderShipmentStatusChanged;
use Symfony\Contracts\EventDispatcher\Event;

final class WaitingForSendShipmentSmsNotificationListener extends AbstractEditableNotificationListener
{
    protected static function getEvents(): array
    {
        return [
            OrderShipmentStatusChanged::class,
        ];
    }

    protected function shouldBail(Event $event): bool
    {
        return $event->getNewStatus() !== OrderShipmentStatus::WAITING_FOR_SEND;
    }

    protected function getDTO(Event $event): WaitingForSendShipmentSmsNotificationDTO
    {
        return new WaitingForSendShipmentSmsNotificationDTO($event->getOrderShipment()->getOrder());
    }
}
