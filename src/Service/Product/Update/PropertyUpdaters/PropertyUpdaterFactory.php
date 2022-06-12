<?php

namespace App\Service\Product\Update\PropertyUpdaters;

use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class PropertyUpdaterFactory
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected BrandRepository $brandRepository,
        protected CategoryRepository $categoryRepository,
        protected MessageBusInterface $bus
    ) {
    }

    public function makeTitleUpdater(): TitleUpdater
    {
        return new TitleUpdater();
    }

    public function makeBrandUpdater(): BrandUpdater
    {
        return new BrandUpdater($this->brandRepository);
    }

    public function makeCategoryUpdater(): CategoryUpdater
    {
        return new CategoryUpdater($this->categoryRepository);
    }

    public function makeSpecificationsUpdater(): SpecificationsUpdater
    {
        return new SpecificationsUpdater();
    }

    public function makeImageUpdater(): ImageUpdater
    {
        return new ImageUpdater($this->bus);
    }
}
