<?php

namespace App\DataFixtures;

use App\Entity\ProductAttribute;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductAttributeFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'product_attribute_1',
            $this->createProductAttribute(
                'product_1',
                'attribute_integer',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_2',
            $this->createProductAttribute(
                'product_1',
                'attribute_boolean',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_3',
            $this->createProductAttribute(
                'product_1',
                'attribute_text_not_multiple',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_4',
            $this->createProductAttribute(
                'product_1',
                'attribute_text_is_multiple',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_5',
            $this->createProductAttribute(
                'product_1',
                'attribute_list',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_6',
            $this->createProductAttribute(
                'product_2',
                'attribute_integer',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_7',
            $this->createProductAttribute(
                'product_2',
                'attribute_boolean',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_8',
            $this->createProductAttribute(
                'product_2',
                'attribute_text_not_multiple',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_9',
            $this->createProductAttribute(
                'product_2',
                'attribute_text_is_multiple',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_10',
            $this->createProductAttribute(
                'product_2',
                'attribute_list',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_11',
            $this->createProductAttribute(
                'product_4',
                'attribute_integer',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_12',
            $this->createProductAttribute(
                'product_4',
                'attribute_boolean',
            )
        );
        $this->setReferenceAndPersist(
            'product_attribute_13',
            $this->createProductAttribute(
                'product_4',
                'attribute_text_is_multiple',
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
            ProductFixtures::class,
            AttributeFixtures::class,
        ];
    }

    private function createProductAttribute(
        string $product,
        string $attribute,
    ): ProductAttribute {
        return (new ProductAttribute())
            ->setProduct($this->getReference($product))
            ->setAttribute($this->getReference($attribute));
    }
}
