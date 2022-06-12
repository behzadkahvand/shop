<?php

namespace App\Messaging\Messages\Command\Notification;

class SmsNotification
{
    protected string $interface;

    public function __construct(protected Recipient $recipient, protected string $message, protected string $code)
    {
        $this->interface = php_sapi_name();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRecipient(): Recipient
    {
        return $this->recipient;
    }

    public function getInterface(): string
    {
        return $this->interface;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
