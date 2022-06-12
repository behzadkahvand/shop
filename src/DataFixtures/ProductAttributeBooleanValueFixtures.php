<?php

namespace App\DataFixtures;

use App\Entity\ProductAttributeBooleanValue;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductAttributeBooleanValueFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createProductAttributeBooleanValue(
                'product_attribute_2',
                true,
            )
        );
        $this->manager->persist(
            $this->createProductAttributeBooleanValue(
                'product_attribute_7',
                false,
            )
        );
        $this->manager->persist(
            $this->createProductAttributeBooleanValue(
                'product_attribute_12',
                true,
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

    private function createProductAttributeBooleanValue(
        string $productAttribute,
        bool $value,
    ): ProductAttributeBooleanValue {
        return (new ProductAttributeBooleanValue())
            ->setProductAttribute($this->getReference($productAttribute))
            ->setValue($value);
    }
}
