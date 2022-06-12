<?php

namespace App\Service\Inventory\InitialInventoryStatus;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Dictionary\InventoryStatus;
use App\Entity\Inventory;
use App\Service\Configuration\ConfigurationServiceInterface;

class InitialInventoryStatusService
{
    public function __construct(protected ConfigurationServiceInterface $configurationService)
    {
    }

    public function set(Inventory $inventory, int $oldLeadTime, int $oldStock): void
    {
        $configuration      = $this->configurationService->findByCode(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS);
        $checkInitialStatus = $configuration && (bool)$configuration->getValue();

        if (!$checkInitialStatus) {
            return;
        }

        $leadTime = $inventory->getLeadTime();

        if (
            $leadTime === 0 &&
            ($oldLeadTime !== 0 || $oldStock < $inventory->getSellerStock())
        ) {
            $inventory->setStatus(InventoryStatus::WAIT_FOR_CONFIRM);
        }
    }
}
