<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\LogInventoryPriceChange;
use App\Repository\InventoryRepository;
use App\Service\Log\DataLoggerService;
use App\Service\Product\Logs\InventoryLogService;
use App\Service\ProductVariant\Exceptions\InventoryNotFoundException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class LogInventoryPriceChangeHandler implements MessageHandlerInterface
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private DataLoggerService $dataLoggerService,
        private InventoryLogService $inventoryLogService
    ) {
    }

    public function __invoke(LogInventoryPriceChange $logInventoryPriceChange): void
    {
        $inventoryId = $logInventoryPriceChange->getInventoryId();

        $inventory = $this->inventoryRepository->find($inventoryId);

        if (!$inventory) {
            throw new InventoryNotFoundException(sprintf('it is not possible to log inventory price change history %d', $inventoryId));
        }

        $inventoryPriceHistoryData = $this->inventoryLogService->makeInventoryPriceHistoryDTO($inventory, $logInventoryPriceChange);

        $this->dataLoggerService->logInventoryPriceChange($inventoryPriceHistoryData);
    }
}
