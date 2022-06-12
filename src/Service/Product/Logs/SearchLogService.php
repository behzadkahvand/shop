<?php

namespace App\Service\Product\Logs;

use App\Messaging\Messages\Command\Product\LogSearch;
use Symfony\Component\Messenger\MessageBusInterface;

class SearchLogService
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function dispatchSearchLogMsg(string $term, int $resultCount, ?int $customerId): void
    {
        $this->messageBus->dispatch(new LogSearch($term, $resultCount, $customerId));
    }
}
