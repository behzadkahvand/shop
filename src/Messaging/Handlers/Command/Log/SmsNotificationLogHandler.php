<?php

namespace App\Messaging\Handlers\Command\Log;

use App\Document\SmsLog\Recipient;
use App\Document\SmsLog\SmsLog;
use App\Messaging\Messages\Command\Log\SmsNotificationLog;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SmsNotificationLogHandler implements MessageHandlerInterface
{
    public function __construct(protected DocumentManager $manager)
    {
    }

    public function __invoke(SmsNotificationLog $notificationLog): void
    {
        $smsNotification       = $notificationLog->getSmsNotification();
        $notificationRecipient = $smsNotification->getRecipient();
        $log                   = new SmsLog();
        $recipient             = new Recipient();

        $recipient->setMobile($notificationRecipient->getMobile())
                  ->setName($notificationRecipient->getName())
                  ->setUserId($notificationRecipient->getUserId())
                  ->setUserType($notificationRecipient->getUserType());

        $log->setContent($smsNotification->getMessage())
            ->setCode($smsNotification->getCode())
            ->setInterface($smsNotification->getInterface())
            ->setRecipient($recipient);

        $this->manager->persist($log);
        $this->manager->flush();
    }
}
