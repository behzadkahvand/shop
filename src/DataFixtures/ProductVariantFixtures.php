<?php

namespace App\DataFixtures;

use App\Entity\ProductVariant;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductVariantFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'product_variant_1',
            $this->createProductVariant(
                'product_1',
                ['product_option_values_red', 'product_option_values_physical', 'product_option_values_shoe_size_42']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_2',
            $this->createProductVariant(
                'product_2',
                ['product_option_values_green', 'product_option_values_sam_service']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_3',
            $this->createProductVariant(
                'product_2',
                ['product_option_values_red', 'product_option_values_sam_service']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_4',
            $this->createProductVariant(
                'product_3',
                ['product_option_values_yellow', 'product_option_values_physical', 'product_option_values_dress_size_medium']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_5',
            $this->createProductVariant(
                'product_4',
                ['product_option_values_blue', 'product_option_values_physical', 'product_option_values_baby_dress_size_medium']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_6',
            $this->createProductVariant(
                'product_5',
                ['product_option_values_blue', 'product_option_values_physical']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_7',
            $this->createProductVariant(
                'product_5',
                ['product_option_values_yellow', 'product_option_values_sam_service']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_8',
            $this->createProductVariant(
                'product_5',
                ['product_option_values_green', 'product_option_values_sam_service']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_9',
            $this->createProductVariant(
                'product_7',
                ['product_option_values_green', 'product_option_values_physical']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_10',
            $this->createProductVariant(
                'product_7',
                ['product_option_values_blue', 'product_option_values_sam_service']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_11',
            $this->createProductVariant(
                'product_8',
                ['product_option_values_blue', 'product_option_values_physical']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_12',
            $this->createProductVariant(
                'product_8',
                ['product_option_values_red', 'product_option_values_physical']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_13',
            $this->createProductVariant(
                'product_9',
                ['product_option_values_green', 'product_option_values_physical']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_14',
            $this->createProductVariant(
                'product_12',
                ['product_option_values_green', 'product_option_values_physical']
            )
        );
        $this->setReferenceAndPersist(
            'product_variant_15',
            $this->createProductVariant(
                'product_13',
                ['product_option_values_green', 'product_option_values_physical']
            )
        );

        $this->manager->flush();
    }

    private function createProductVariant(
        string $product,
        array $productOptionValues
    ): ProductVariant {
        $productVariant = (new ProductVariant())->setProduct($this->getReference($product));

        foreach ($productOptionValues as $value) {
            $productVariant->addOptionValue($this->getReference($value));
        }

        return $productVariant;
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            ProductOptionValueFixtures::class,
            ProductFixtures::class,
        ];
    }
}
