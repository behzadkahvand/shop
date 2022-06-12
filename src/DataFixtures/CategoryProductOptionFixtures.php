<?php

namespace App\DataFixtures;

use App\Entity\CategoryProductOption;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CategoryProductOptionFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "category_option_1",
            $this->createCategoryProductOption(
                'category_mobile',
                'product_option_color'
            )
        );

        $this->setReferenceAndPersist(
            "category_option_2",
            $this->createCategoryProductOption(
                'category_mobile',
                'product_option_guarantee',
                ['product_option_values_physical', 'product_option_values_sam_service']
            )
        );

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            ProductOptionFixtures::class,
            ProductOptionValueFixtures::class,
        ];
    }

    private function createCategoryProductOption(
        string $category,
        string $productOption,
        array $productOptionValues = []
    ): CategoryProductOption {
        $categoryProductOption = (new CategoryProductOption())->setCategory($this->getReference($category))
                                                              ->setProductOption($this->getReference($productOption));

        if ($productOptionValues) {
            foreach ($productOptionValues as $optionValue) {
                $categoryProductOption->addOptionValue($this->getReference($optionValue));
            }
        }

        return $categoryProductOption;
    }
}
