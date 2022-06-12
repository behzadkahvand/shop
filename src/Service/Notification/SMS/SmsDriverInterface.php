<?php

namespace App\Service\Notification\SMS;

interface SmsDriverInterface
{
    public function sendMessage(string $mobile, string $message): void;

    public static function getName(): string;
}
