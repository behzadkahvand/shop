<?php

namespace App\Service\Product\Logs;

use App\Entity\Inventory;
use DateTime;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Security;

class InventoryChangeLogListener
{
    private InventoryLogService $inventoryLogService;

    private ?int $adminId;

    public function __construct(InventoryLogService $inventoryLogService, Security $security)
    {
        $this->inventoryLogService = $inventoryLogService;
        $this->adminId             = $this->getCurrentAdminId($security);
    }

    public function onInventoryPostInsert(Inventory $inventory): void
    {
        $this->inventoryLogService->dispatchInventoryPriceChangeMessage(
            $inventory->getId(),
            0,
            0,
            $this->adminId
        );
    }

    public function onInventoryPreUpdate(Inventory $inventory, PreUpdateEventArgs $args): void
    {
        if ($this->inventoryLogService->hasInventoryPriceChanged($args)) {
            $this->inventoryLogService->dispatchInventoryPriceChangeMessage(
                $inventory->getId(),
                $args->hasChangedField('price') ? $args->getOldValue('price') : null,
                $args->hasChangedField('finalPrice') ? $args->getOldValue('finalPrice') : null,
                $this->adminId
            );
        }

        $inventoryLogData = $this->inventoryLogService->checkInventoryIsLoggable($inventory, $args);
        if ($inventoryLogData->isChangeStatus()) {
            $this->inventoryLogService->dispatchInventoryLogMessage(
                $inventoryLogData->addLoggableProperty("updatedBy", $this->adminId)
                                 ->addLoggableProperty("updatedAt", new DateTime("now"))
            );
        }
    }

    private function getCurrentAdminId(Security $security): ?int
    {
        $admin = $security->getUser();
        if (!$admin) {
            return null;
        }

        if (method_exists($admin, 'getId') && $admin->getId()) {
            return $admin->getId();
        }

        return null;
    }
}
