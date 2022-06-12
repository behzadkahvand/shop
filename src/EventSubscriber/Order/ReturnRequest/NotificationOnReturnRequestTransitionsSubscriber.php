<?php

namespace App\EventSubscriber\Order\ReturnRequest;

use App\Events\Order\ReturnRequest\ReturnRequestStatusUpdated;
use App\Service\Notification\NotificationService;
use App\Service\Order\ReturnRequest\Notification\ReturnRequestNotificationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationOnReturnRequestTransitionsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected NotificationService $notificationService,
        protected ReturnRequestNotificationFactory $notificationFactory
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ReturnRequestStatusUpdated::class => 'onUpdateStatus'
        ];
    }

    public function onUpdateStatus(ReturnRequestStatusUpdated $event): void
    {
        $notification = $this->notificationFactory->make($event->getReturnRequest());

        if (isset($notification)) {
            $this->notificationService->send($notification);
        }
    }
}
