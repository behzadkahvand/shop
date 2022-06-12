<?php

namespace App\Service\Inventory\DepotInventory;

use App\Repository\InventoryRepository;
use App\Service\Notification\DTOs\Seller\NotifyDepotInventorySmsNotificationDTO;
use App\Service\Notification\NotificationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotifyDepotInventorySmsHandler implements MessageHandlerInterface
{
    public function __construct(
        private NotificationService $notificationService,
        private InventoryRepository $repository
    ) {
    }

    public function __invoke(DepotInventoryMessage $message): void
    {
        foreach ($this->getInventoriesByIds($message->getInventories()) as $inventory) {
            $this->notificationService->send(
                new NotifyDepotInventorySmsNotificationDTO($inventory)
            );
        }
    }

    protected function getInventoriesByIds(array $inventoryIds): array
    {
        return $this->repository->findBy(['id' => $inventoryIds]);
    }
}
