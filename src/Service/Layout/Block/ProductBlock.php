<?php

namespace App\Service\Layout\Block;

use App\Repository\ProductRepository;
use App\Service\Layout\CacheBlock\CacheableBlockInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ProductBlock
 */
class ProductBlock extends Block implements CacheableBlockInterface
{
    public function __construct(private ProductRepository $repository, private EntityManagerInterface $em)
    {
    }

    public function getCode(): string
    {
        return "products";
    }

    public function generate(array $context = []): array
    {
        $ids = $this->get($context, 'products');

        if (0 === count($ids)) {
            return [];
        }

        $products = $this->repository->listByIds($ids);

        $this->em->clear();

        return $products;
    }

    public function getCacheExpiry(): int
    {
        return 360;
    }

    public function getCacheSignature(array $context = []): string
    {
        return collect($this->get($context, 'products'))->unique()->implode('_');
    }
}
