<?php

namespace App\Service\SellerForm;

use App\Entity\MarketingSellerLanding;
use App\Messaging\Messages\Command\Notification\SendSellerFormMail;
use Symfony\Component\Messenger\MessageBusInterface;

class SellerFormService
{
    private MessageBusInterface $messageBus;

    private string $sellerFormMail;

    public function __construct(MessageBusInterface $messageBus, string $sellerFormEmail)
    {
        $this->messageBus     = $messageBus;
        $this->sellerFormMail = $sellerFormEmail;
    }

    public function sendMail(MarketingSellerLanding $marketingSellerLanding): void
    {
        $this->messageBus->dispatch(
            async_message(new SendSellerFormMail($this->sellerFormMail, $marketingSellerLanding))
        );
    }
}
