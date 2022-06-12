<?php

namespace App\Messaging\Messages\Command\Notification;

use App\DTO\Customer\ContactUsData;

final class SendContactUsMail
{
    private const SUBJECT_PREFIX = 'ارتباط با تیمچه : ';

    private const MAIL_FROM = 'no-reply@timcheh.com';

    private string $body;

    public function __construct(private string $contactUsEmail, private ContactUsData $contactUsData)
    {
        $this->body = (string)$contactUsData;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getTo(): string
    {
        return $this->contactUsEmail;
    }

    public function getFrom(): string
    {
        return self::MAIL_FROM;
    }

    public function getSubject(): string
    {
        return self::SUBJECT_PREFIX . $this->contactUsData->getSubject();
    }
}
