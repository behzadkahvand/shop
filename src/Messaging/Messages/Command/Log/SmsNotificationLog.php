<?php

namespace App\Messaging\Messages\Command\Log;

use App\Messaging\Messages\Command\Notification\SmsNotification;

class SmsNotificationLog
{
    public function __construct(private SmsNotification $smsNotification)
    {
    }

    public function getSmsNotification(): SmsNotification
    {
        return $this->smsNotification;
    }
}
