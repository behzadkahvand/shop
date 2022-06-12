<?php

namespace App\DataFixtures;

use App\Entity\CategoryBrandSellerProductOption;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CategoryBrandSellerProductOptionFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $setProductOption = (new CategoryBrandSellerProductOption())
            ->setCategory($this->getReference("category_tv"))
            ->setBrand($this->getReference("brand_1"))
            ->setProductOption($this->getReference("product_option_guarantee"))
            ->addValue($this->getReference("product_option_values_sam_service"))
            ->addValue($this->getReference("product_option_values_physical"));

        $this->addReference('category_brand_seller_product_option_1', $setProductOption);

        $this->manager->persist($setProductOption);
        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            BrandFixtures::class,
            ProductOptionFixtures::class,
            ProductOptionValueFixtures::class,
        ];
    }
}
