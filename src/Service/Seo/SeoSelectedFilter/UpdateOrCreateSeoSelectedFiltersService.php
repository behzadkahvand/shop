<?php

namespace App\Service\Seo\SeoSelectedFilter;

use App\DTO\Admin\Seo\SeoSelectedFilterData;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Seo\SeoSelectedBrandFilter;
use App\Repository\Seo\SeoSelectedBrandFilterRepository;
use App\Service\Utils\GenerateSoeMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class UpdateOrCreateSeoSelectedFiltersService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SeoSelectedBrandFilterRepository $seoSelectedBrandFilterRepository,
        private GenerateSoeMetadata $generateSoeMetadata,
        private AddSeoSelectedFilterService $addSeoSelectedFilter
    ) {
    }

    public function updateOrCreate(Category $category, array $brands): void
    {
        try {
            array_map(function ($brand) use ($category) {
                $seoSelectedFilter = $this->seoSelectedBrandFilterRepository->findOneByCategoryAndBrand(
                    $category->getId(),
                    $brand['id']
                );

                if ($seoSelectedFilter) {
                        $this->updateSeoSelectedFilters($seoSelectedFilter, $category->getTitle(), $brand['title']);
                } else {
                    $this->createSeoSelectedFilters($category, $brand);
                }
            }, $brands);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    private function createSeoSelectedFilters(Category $category, array $brand): void
    {
        $dto = (new SeoSelectedFilterData())
            ->setCategory($category)
            ->setBrand($this->entityManager->getReference(Brand::class, $brand['id']))
            ->setTitle($this->generateSoeMetadata->title($category->getTitle(), $brand['title']))
            ->setMetaDescription($this->generateSoeMetadata->metaDescription($category->getTitle(), $brand['title']))
            ->setStarred(true);

        $this->addSeoSelectedFilter->perform($dto);
    }

    private function updateSeoSelectedFilters(SeoSelectedBrandFilter $seoSelectedFilter, string $categoryName, string $brandName): void
    {
        $update = 0;
        if (!$seoSelectedFilter->getTitle()) {
            $seoSelectedFilter->setTitle($this->generateSoeMetadata->title($categoryName, $brandName));
            $update = 1;
        }

        if (!$seoSelectedFilter->getMetaDescription()) {
            $seoSelectedFilter->setMetaDescription($this->generateSoeMetadata->metaDescription($categoryName, $brandName));
            $update = 1;
        }

        if ($update) {
            $this->entityManager->flush();
        }
    }
}
