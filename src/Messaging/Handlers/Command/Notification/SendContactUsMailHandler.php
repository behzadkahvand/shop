<?php

namespace App\Messaging\Handlers\Command\Notification;

use App\Messaging\Messages\Command\Notification\SendContactUsMail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Email;

final class SendContactUsMailHandler implements MessageHandlerInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function __invoke(SendContactUsMail $mailNotification): void
    {
        $email = (new Email())
            ->from($mailNotification->getFrom())
            ->to($mailNotification->getTo())
            ->subject($mailNotification->getSubject())
            ->text(
                $mailNotification->getBody()
            );

        $this->mailer->send($email);
    }
}
