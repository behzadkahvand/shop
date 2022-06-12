<?php

namespace App\Service\ProductVariant\Configurator;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Service\Configuration\ConfigurationService;
use App\Service\ProductVariant\CreateProductVariantWithInventoryService;

class CreateProductVariantWithInventoryConfigurator
{
    protected ConfigurationService $configurationService;

    public function __construct(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    public function configure(CreateProductVariantWithInventoryService $service): void
    {
        $configuration = $this->configurationService
            ->findByCode(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS);

        $checkInitialInventoryStatus = $configuration ? (bool)$configuration->getValue() : false;
        $service->setCheckInitialStatus($checkInitialInventoryStatus);
    }
}
