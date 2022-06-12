<?php

namespace App\Messaging\Handlers\Command\Notification;

use App\Messaging\Messages\Command\Log\SmsNotificationLog;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\SMS\SmsService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SmsNotificationHandler implements MessageHandlerInterface
{
    public function __construct(protected SmsService $smsService, private MessageBusInterface $messageBus)
    {
    }

    public function __invoke(SmsNotification $notification): void
    {
        $this->smsService->sendMessage($notification->getRecipient()->getMobile(), $notification->getMessage());

        $this->log($notification);
    }

    private function log(SmsNotification $notification): void
    {
        $message = new SmsNotificationLog($notification);

        $this->messageBus->dispatch($message);
    }
}
