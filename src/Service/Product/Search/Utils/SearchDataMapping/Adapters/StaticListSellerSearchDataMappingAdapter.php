<?php

namespace App\Service\Product\Search\Utils\SearchDataMapping\Adapters;

use App\Dictionary\ProductSearchDataMappingDictionary;
use App\Dictionary\WebsiteAreaDictionary;
use App\Service\Product\Search\Utils\SearchDataMapping\SearchDataMappingInterface;

/**
 * Class StaticListSellerSearchDataMappingAdapter
 */
class StaticListSellerSearchDataMappingAdapter implements SearchDataMappingInterface
{
    private const MAPPING = [
        WebsiteAreaDictionary::AREA_CUSTOMER => ProductSearchDataMappingDictionary::CUSTOMER_SELLER_SEARCH_AREA_MAPPING
    ];

    /**
     * @inheritDoc
     */
    public function hasMappedFilter(string $filter, string $area): bool
    {
        return isset(self::MAPPING[$area]['filters'][$filter]);
    }

    /**
     * @inheritDoc
     */
    public function getMappedFilter(string $filter, string $area): ?string
    {
        return self::MAPPING[$area]['filters'][$filter] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function hasMappedSort(string $sort, string $area): bool
    {
        return isset(self::MAPPING[$area]['sorts'][$sort]);
    }

    /**
     * @inheritDoc
     */
    public function getMappedSort(string $sort, string $area): ?string
    {
        return self::MAPPING[$area]['sorts'][$sort] ?? null;
    }
}
