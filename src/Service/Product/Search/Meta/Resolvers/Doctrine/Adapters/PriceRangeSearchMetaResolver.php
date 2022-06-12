<?php

namespace App\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters;

use App\Dictionary\WebsiteAreaDictionary;
use App\Entity\Product;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\Resolvers\Doctrine\DoctrineSearchMetaResolverInterface;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\Utils\Doctrine\InventoryPriceRangeInterface;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;

final class PriceRangeSearchMetaResolver implements DoctrineSearchMetaResolverInterface
{
    public function __construct(
        private InventoryPriceRangeInterface $inventoryPriceRange,
        private QueryBuilderFilterService $filterService,
        private WebsiteAreaService $websiteAreaService
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve($query, SearchData $data, Pagination $pagination): array
    {
        if (
            !$data instanceof DoctrineSearchData ||
            !$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_CUSTOMER)
        ) {
            return [];
        }

        $joinMap = $this->filterService::getJoinMap();

        $filters = $data->getFilters();

        if (isset($filters['buyBox.finalPrice'])) {
            unset($filters['buyBox.finalPrice']);
        } elseif (isset($filters['buyBox.id'])) {
            unset($filters['buyBox.id']);
        }

        if (isset($filters['productVariants.inventories.finalPrice'])) {
            unset($filters['productVariants.inventories.finalPrice']);
        } elseif (isset($filters['productVariants.inventories.id'])) {
            unset($filters['productVariants.inventories.id']);
        }

        $queryBuilder = $this->filterService->filter(Product::class, [
            'filter' => $filters,
        ]);

        $meta = [
            'priceRange' => $this->inventoryPriceRange->getPriceRange($queryBuilder, $filters),
        ];

        $this->filterService::setJoinMap($joinMap);

        return $meta;
    }
}
