<?php

namespace App\Service\Notification\EventListeners\SMS;

use App\Dictionary\OrderStatus;
use App\Entity\OrderStatusLog;
use App\Service\Notification\DTOs\Customer\Order\FirstTimeCallFailedSmsNotificationDTO;
use App\Service\Notification\EventListeners\AbstractEditableNotificationListener;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use Symfony\Contracts\EventDispatcher\Event;

final class FirstTimeCallFailedSmsNotificationListener extends AbstractEditableNotificationListener
{
    protected static function getEvents(): array
    {
        return [
            OrderStatusChanged::class,
        ];
    }

    protected function shouldBail(Event $event): bool
    {
        if ($event->getNewStatus() !== OrderStatus::CALL_FAILED) {
            return true;
        }

        $logs = $event->getOrder()->getOrderStatusLogs()->filter(
            fn (OrderStatusLog $log) => $log->getStatusTo() === OrderStatus::CALL_FAILED
        );

        $callFailedForTheFirstTime = $logs->count() === 1;

        if (! $callFailedForTheFirstTime) {
            return true;
        }

        return false;
    }

    protected function getDTO(Event $event): FirstTimeCallFailedSmsNotificationDTO
    {
        return new FirstTimeCallFailedSmsNotificationDTO($event->getOrder());
    }
}
