<?php

namespace App\Service\Seo;

use App\Messaging\Messages\Command\Seo\AddTitleAndMetaDescription;
use Symfony\Component\Messenger\MessageBusInterface;

class AddTitleAndMetaDescriptionService
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function handle(array $categoryIds): void
    {
        foreach ($categoryIds as $categoryId) {
            $message = new AddTitleAndMetaDescription($categoryId);

            $this->messageBus->dispatch($message);
        }
    }
}
