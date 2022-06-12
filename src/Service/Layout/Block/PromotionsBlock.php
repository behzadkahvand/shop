<?php

namespace App\Service\Layout\Block;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\Layout\CacheBlock\CacheableBlockInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

/**
 * Class PromotionsBlock
 */
final class PromotionsBlock extends Block implements CacheableBlockInterface
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $em
    ) {
    }

    public function generate(array $context = []): array
    {
        $codes = $this->get($context, 'promotions');

        if (0 === count($codes)) {
            return [];
        }

        $results = [];

        if (in_array('all', array_map('strtolower', $codes), true)) {
            $results = [
                'all' => $this->productRepository->listByCategoriesWithPromotion([], 10, true)
                                                 ->getQuery()
                                                 ->setHint(Query::HINT_REFRESH, true)
                                                 ->getResult(),
            ];

            $this->em->clear();

            return $results;
        }

        $categories = $this->categoryRepository->getReferenceByCodes(collect($codes)->unique()->toArray());

        foreach ($categories as $category) {
            $products = $this->productRepository->listByCategoriesWithPromotion($this->getCategoryIds($category), 10, true)
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
        return 'promotions';
    }

    public function getCacheExpiry(): int
    {
        return 360;
    }

    public function getCacheSignature(array $context = []): string
    {
        return collect($this->get($context, 'promotions'))->unique()->implode('_');
    }

    private function getCategoryIds(Category $category): array
    {
        return explode(',', $this->categoryRepository->getCategoryLeafIdsForCategory($category));
    }
}
