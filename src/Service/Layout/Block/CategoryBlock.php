<?php

namespace App\Service\Layout\Block;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\Layout\CacheBlock\CacheableBlockInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

class CategoryBlock extends Block implements CacheableBlockInterface
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $em
    ) {
    }

    public function generate(array $context = []): array
    {
        $results    = [];
        $codes      = $this->get($context, 'categories');
        $categories = $this->categoryRepository->getReferenceByCodes(collect($codes)->unique()->toArray());

        foreach ($categories as $category) {
            $products = $this->productRepository->listByCategories($this->getCategoryIds($category), 10)
                                                ->getQuery()
                                                ->setHint(Query::HINT_REFRESH, true)
                                                ->getResult();

            $results[$category->getCode()] = $products;

            $this->em->clear();
        }

        return $results;
    }

    public function getCode(): string
    {
        return 'categories';
    }

    public function getCacheExpiry(): int
    {
        return 360;
    }

    public function getCacheSignature(array $context = []): string
    {
        return collect($this->get($context, 'categories'))->unique()->implode('_');
    }

    private function getCategoryIds(Category $category): array
    {
        return explode(',', $this->categoryRepository->getCategoryLeafIdsForCategory($category));
    }
}
