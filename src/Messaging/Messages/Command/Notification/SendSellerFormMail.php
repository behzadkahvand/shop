<?php

namespace App\Messaging\Messages\Command\Notification;

use App\Entity\MarketingSellerLanding;

final class SendSellerFormMail
{
    private const DEFAULT_FROM_EMAIL = 'no-reply@timcheh.com';

    private string $body;

    public function __construct(private string $sellerFormMail, MarketingSellerLanding $contactUsData)
    {
        $this->body = (string)$contactUsData;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getTo(): string
    {
        return $this->sellerFormMail;
    }

    public function getFrom(): string
    {
        return self::DEFAULT_FROM_EMAIL;
    }

    public function getSubject(): string
    {
        return 'ثبت همکاری در پنل فروشندگان تیمچه';
    }
}
