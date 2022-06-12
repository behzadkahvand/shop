<?php

namespace App\Service\Layout\OnSaleBlock;

use App\Dictionary\ConfigurationCodeDictionary;

class OnSaleInventoryBlock extends OnSaleBlock
{
    public function getConfigCode(): string
    {
        return ConfigurationCodeDictionary::ON_SALE_INVENTORY;
    }

    public function getCode(): string
    {
        return 'onSaleInventories';
    }

    public function findProducts(): array
    {
        $this->disableQueryFilters();
        return $this->productRepository->findProductsByInventoryIds($this->ids);
    }

    public function disableQueryFilters(): void
    {
        $filters = [
            'inventoryIsActive',
            'inventoryHasStock',
            'inventoryConfirmedStatus',
        ];

        foreach ($filters as $filter) {
            $this->entityManager->getFilters()->disable($filter);
        }
    }
}
