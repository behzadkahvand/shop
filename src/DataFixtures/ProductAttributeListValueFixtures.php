<?php

namespace App\DataFixtures;

use App\Entity\ProductAttributeListValue;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductAttributeListValueFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createProductAttributeListValue(
                'product_attribute_5',
                'attribute_list_item_4',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeListValue(
                'product_attribute_5',
                'attribute_list_item_5',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeListValue(
                'product_attribute_10',
                'attribute_list_item_4',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeListValue(
                'product_attribute_10',
                'attribute_list_item_5',
            )
        );
        $this->manager->persist(
            $this->createProductAttributeListValue(
                'product_attribute_10',
                'attribute_list_item_6',
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
            AttributeListItemFixtures::class,
        ];
    }

    private function createProductAttributeListValue(
        string $productAttribute,
        string $attributeListItem,
    ): ProductAttributeListValue {
        return (new ProductAttributeListValue())
            ->setProductAttribute($this->getReference($productAttribute))
            ->setValue($this->getReference($attributeListItem));
    }
}
