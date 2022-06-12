<?php

namespace App\Service\Layout\OnSaleBlock;

use App\Dictionary\ConfigurationCodeDictionary;

class OnSaleProductBlock extends OnSaleBlock
{
    public function getConfigCode(): string
    {
        return ConfigurationCodeDictionary::ON_SALE_PRODUCTS;
    }

    public function getCode(): string
    {
        return 'onSaleProducts';
    }

    public function findProducts(): array
    {
        return $this->productRepository->listByIds($this->ids);
    }
}
