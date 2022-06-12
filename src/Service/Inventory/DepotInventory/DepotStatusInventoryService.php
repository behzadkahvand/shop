<?php

namespace App\Service\Inventory\DepotInventory;

use App\Repository\InventoryRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class DepotStatusInventoryService
{
    public function __construct(private InventoryRepository $repository, private MessageBusInterface $bus)
    {
    }

    public function handle($orderId): void
    {
        $inventories = $this->getDepotInventoryByOrder($orderId);

        if (!count($inventories)) {
            return;
        }

        $inventories = array_map(fn($inv) => $inv->getId(), $inventories);

        $this->bus->dispatch(async_message(new DepotInventoryMessage($inventories)));
    }

    protected function getDepotInventoryByOrder($orderId): array
    {
        return $this->repository->findDepotInventoryByOrder($orderId);
    }
}
