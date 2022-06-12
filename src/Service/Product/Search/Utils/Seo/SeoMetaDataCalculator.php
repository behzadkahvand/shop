<?php

namespace App\Service\Product\Search\Utils\Seo;

use App\Entity\Brand;
use App\Entity\Category;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\Seo\SeoSelectedBrandFilterRepository;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\Utils\Seo\ValueObjects\SeoMetaDataValueObject;

class SeoMetaDataCalculator
{
    protected const TITLE_PREFIX = 'خرید و قیمت ';

    protected const TITLE_POSTFIX = 'تیمچه';

    protected CategoryRepository $categoryRepository;

    protected BrandRepository $brandRepository;

    protected SeoSelectedBrandFilterRepository $seoSelectedBrandFilterRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        BrandRepository $brandRepository,
        SeoSelectedBrandFilterRepository $seoSelectedBrandFilterRepository
    ) {
        $this->categoryRepository               = $categoryRepository;
        $this->brandRepository                  = $brandRepository;
        $this->seoSelectedBrandFilterRepository = $seoSelectedBrandFilterRepository;
    }

    public function getData(SearchData $data): SeoMetaDataValueObject
    {
        $category = $this->findCategoryByCode($data->getCategoryCode());

        $seoMetaData = new SeoMetaDataValueObject();

        if (!$category) {
            return $seoMetaData;
        }

        $seoMetaData->setCategory($category)
                    ->setTitle($category->getPageTitle())
                    ->setDescription($category->getDescription())
                    ->setMetaDescription($category->getMetaDescription());

        $filters = $data->getFilters();

        $brandIds = isset($filters['brand.id']['in']) ? explode(',', $filters['brand.id']['in']) : [];

        if (empty($brandIds) || count($brandIds) > 1) {
            return $seoMetaData;
        }

        $brandId = (int)current($brandIds);

        $seoSelectedFilter = $this->seoSelectedBrandFilterRepository->findOneByCategoryAndBrand(
            $category->getId(),
            $brandId
        );

        if (!$seoSelectedFilter) {
            return $seoMetaData;
        }

        $brand = $this->getBrand($brandId);

        if ($brand) {
            $title = self::TITLE_PREFIX . $category->getTitle() . ' ' . $brand->getTitle() . ' | ' . self::TITLE_POSTFIX;

            $seoMetaData->setTitle($title);
        }

        $seoTitle           = $seoSelectedFilter->getTitle();
        $seoDescription     = $seoSelectedFilter->getDescription();
        $seoMetaDescription = $seoSelectedFilter->getMetaDescription();

        if ($seoTitle) {
            $seoMetaData->setTitle($seoTitle);
        }

        if ($seoDescription) {
            $seoMetaData->setDescription($seoDescription);
        }

        if ($seoMetaDescription) {
            $seoMetaData->setMetaDescription($seoMetaDescription);
        }

        return $seoMetaData;
    }

    protected function findCategoryByCode(?string $categoryCode): ?Category
    {
        return $categoryCode ? $this->categoryRepository->findOneBy(['code' => $categoryCode]) : null;
    }

    protected function getBrand(int $brandId): ?Brand
    {
        return $this->brandRepository->findOneBy(['id' => $brandId]);
    }
}
