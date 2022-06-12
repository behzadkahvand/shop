<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\LogInventory;
use App\Service\Log\DataLoggerService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class LogInventoryHandler implements MessageHandlerInterface
{
    public function __construct(private DataLoggerService $dataLoggerService)
    {
    }

    public function __invoke(LogInventory $logInventory): void
    {
        $this->dataLoggerService->logInventory($logInventory->getLoggableProperties());
    }
}
