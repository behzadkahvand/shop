<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\LogSearch;
use App\Service\Log\DataLoggerService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class LogSearchHandler implements MessageHandlerInterface
{
    public function __construct(private DataLoggerService $dataLoggerService)
    {
    }

    public function __invoke(LogSearch $logSearch): void
    {
        $this->dataLoggerService->logProductSearch(
            $logSearch->getTerm(),
            $logSearch->getResultCount(),
            $logSearch->getCustomerId()
        );
    }
}
