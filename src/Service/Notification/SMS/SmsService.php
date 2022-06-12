<?php

namespace App\Service\Notification\SMS;

class SmsService
{
    protected SmsDriverInterface $driver;

    public function __construct(SmsDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function sendMessage(string $mobile, string $message): void
    {
        $this->driver->sendMessage($mobile, $message);
    }
}
