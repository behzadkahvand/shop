<?php

namespace App\DataFixtures;

use App\Entity\ProductAttributeTextValue;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductAttributeTextValueFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_3',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_4',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_4',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_4',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_8',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_9',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_9',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_9',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_13',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeTextValue(
                'product_attribute_13',
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

    private function createProductAttributeTextValue(
        string $productAttribute,
    ): ProductAttributeTextValue {
        return (new ProductAttributeTextValue())
            ->setProductAttribute($this->getReference($productAttribute))
            ->setValue($this->faker->realText(50));
    }
}
