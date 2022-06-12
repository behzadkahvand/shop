<?php

namespace App\DataFixtures;

use App\Entity\ProductAttributeNumericValue;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductAttributeNumericValueFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createProductAttributeNumericValue(
                'product_attribute_1',
                12,
            )
        );
        $this->manager->persist(
            $this->createProductAttributeNumericValue(
                'product_attribute_6',
                -10,
            )
        );
        $this->manager->persist(
            $this->createProductAttributeNumericValue(
                'product_attribute_11',
                -10,
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
            ProductAttributeFixtures::class,
        ];
    }

    private function createProductAttributeNumericValue(
        string $productAttribute,
        int $value,
    ): ProductAttributeNumericValue {
        return (new ProductAttributeNumericValue())
            ->setProductAttribute($this->getReference($productAttribute))
            ->setValue($value);
    }
}
