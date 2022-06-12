<?php

namespace App\Service\ContactUs;

use App\DTO\Customer\ContactUsData;
use App\Messaging\Messages\Command\Notification\SendContactUsMail;
use Symfony\Component\Messenger\MessageBusInterface;

final class ContactUsService
{
    public function __construct(private MessageBusInterface $messageBus, private string $contactUsEmail)
    {
    }

    public function sendMail(ContactUsData $contactUsData): void
    {
        $this->messageBus->dispatch(
            async_message(new SendContactUsMail($this->contactUsEmail, $contactUsData))
        );
    }
}
