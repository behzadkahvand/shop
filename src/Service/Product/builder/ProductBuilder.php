<?php

namespace App\Service\Product\builder;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Seller;
use App\Entity\ShippingCategory;
use App\Repository\CategoryRepository;
use App\Repository\ShippingCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ProductBuilder
{
    public const NORMAL_SHIPPING_CATEGORY_NAME = 'NORMAL';

    protected ?ShippingCategory $shippingCategory = null;
    protected ?Category $category = null;
    protected ?Seller $seller = null;
    protected bool $isActive = false;
    protected bool $isOriginal = false;
    protected string $status = ProductStatusDictionary::DRAFT;
    protected ?string $digikalaDkp = null;

    public function __construct(
        protected ShippingCategoryRepository $shippingCategoryRepository,
        protected CategoryRepository $categoryRepository,
        protected LoggerInterface $logger,
        protected EntityManagerInterface $em
    ) {
    }

    public function withSeller(?Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function withDigikalaDkp(?string $digikalaDkp): self
    {
        $this->digikalaDkp = $digikalaDkp;

        return $this;
    }

    public function build(): Product
    {
        return (new Product())
            ->setStatus($this->status)
            ->setIsActive($this->isActive)
            ->setIsOriginal($this->isOriginal)
            ->setCategory($this->category ?? $this->fetchCategory())
            ->setSeller($this->seller)
            ->setShippingCategory($this->shippingCategory ?? $this->fetchShippingCategory())
            ->setDigikalaDkp($this->digikalaDkp);
    }

    private function fetchCategory(): Category
    {
        return $this->categoryRepository->findOneBy([]);
    }

    private function fetchShippingCategory(): ShippingCategory
    {
        return $this->shippingCategoryRepository->findOneBy([
            'name' => self::NORMAL_SHIPPING_CATEGORY_NAME
        ]);
    }
}
