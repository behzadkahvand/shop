<?php

namespace App\Service\Notification\SMS\Adapters;

use App\Service\Notification\SMS\SmsDriverInterface;
use Psr\Log\LoggerInterface;

class LogSmsDriverAdapter implements SmsDriverInterface
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function sendMessage(string $mobile, string $message): void
    {
        $this->logger->info('SMS Notification Sent!', compact('message', 'mobile'));
    }

    public static function getName(): string
    {
        return 'Log';
    }
}
