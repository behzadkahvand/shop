<?php

namespace App\DataFixtures;

use App\Entity\ProductOptionValue;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductOptionValueFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'product_option_values_red',
            $this->createProductOptionValue(
                'product_option_color',
                'قرمز',
                'red',
                ['hex' => '#FF0000']
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_blue',
            $this->createProductOptionValue(
                'product_option_color',
                'آبی',
                'blue',
                ['hex' => '#0000FF']
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_yellow',
            $this->createProductOptionValue(
                'product_option_color',
                'زرد',
                'yellow',
                ['hex' => '#FFFF00']
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_green',
            $this->createProductOptionValue(
                'product_option_color',
                'سبز',
                'green',
                ['hex' => '#008000']
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_sam_service',
            $this->createProductOptionValue(
                'product_option_guarantee',
                'سام سرویس',
                'sam-service',
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_physical',
            $this->createProductOptionValue(
                'product_option_guarantee',
                'اصالت فیزیکی',
                'physical',
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_shoe_size_42',
            $this->createProductOptionValue(
                'product_option_shoe_size',
                42,
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_shoe_size_43',
            $this->createProductOptionValue(
                'product_option_shoe_size',
                43,
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_dress_size_medium',
            $this->createProductOptionValue(
                'product_option_dress_size',
                'M',
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_dress_size_large',
            $this->createProductOptionValue(
                'product_option_dress_size',
                'L',
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_baby_dress_size_medium',
            $this->createProductOptionValue(
                'product_option_baby_dress_size',
                '۱ تا ۳ ماه',
            )
        );
        $this->setReferenceAndPersist(
            'product_option_values_baby_dress_size_large',
            $this->createProductOptionValue(
                'product_option_baby_dress_size',
                '۶ تا ۹ ماه',
            )
        );

        $this->createMany(
            ProductOptionValue::class,
            11,
            function (ProductOptionValue $productOptionValue, int $count) {
                if ($count == 1 || $count == 2) {
                    $productOptionValue->setOption($this->getReference('product_option_1'));
                } elseif ($count == 3) {
                    $productOptionValue->setOption($this->getReference('product_option_2'));
                } elseif ($count == 4 || $count == 5) {
                    $productOptionValue->setOption($this->getReference('product_option_3'));
                } else {
                    $productOptionValue->setOption($this->getReference('product_option_' . ($count - 2)));
                }
                $productOptionValue->setValue($this->faker->sentence(1));
                $productOptionValue->setAttributes([$this->faker->sentence(1)]);
            },
            true
        );

        $this->manager->flush();
    }

    private function createProductOptionValue(
        string $productOption,
        string $value,
        ?string $code = null,
        array $attributes = [],
    ): ProductOptionValue {
        return (new ProductOptionValue())
            ->setOption($this->getReference($productOption))
            ->setCode($code ?? $value)
            ->setValue($value)
            ->setAttributes($attributes);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            ProductOptionFixtures::class,
        ];
    }
}
