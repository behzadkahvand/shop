<?php

namespace App\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters;

use App\Entity\Brand;
use App\Repository\BrandRepository;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\Resolvers\Doctrine\DoctrineSearchMetaResolverInterface;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;

/**
 * Class BrandSearchMetaResolver
 */
final class BrandSearchMetaResolver implements DoctrineSearchMetaResolverInterface
{
    private BrandRepository $brandRepository;

    /**
     * BrandSearchMetaResolver constructor.
     *
     * @param BrandRepository $brandRepository
     */
    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    /**
     * @inheritDoc
     */
    public function resolve($query, SearchData $data, Pagination $pagination): array
    {
        if (!$data instanceof DoctrineSearchData) {
            return [];
        }

        $filters      = $data->getFilters();
        $brand        = $filters['brand.code'] ?? null;
        $productTitle = $data->getTitle();
        $brandEntity  = $this->getBrand($brand);
        $categoryIds  = [];

        if (isset($filters['category.id'])) {
            $categoryIds = explode(',', $filters['category.id']['in']);

            $excludedCategories = isset($filters['category.id']['nin']) ? explode(',', $filters['category.id']['nin']) : [];

            $categoryIds = array_values(array_diff($categoryIds, $excludedCategories));
        }

        $brands = $this->brandRepository->getBrandsForProductSearch($categoryIds, $productTitle);

        return [
            'brands' => [
                'title'       => $brandEntity ? $brandEntity->getTitle() : null,
                'description' => $brand && $brandEntity ? $brandEntity->getDescription() : null,
                'items'       => $brands,
            ],
        ];
    }

    /**
     * @param string|null $brand
     *
     * @return Brand|null
     */
    private function getBrand(?string $brand): ?Brand
    {
        return $brand ? $this->brandRepository->findOneBy(['code' => $brand]) : null;
    }
}
