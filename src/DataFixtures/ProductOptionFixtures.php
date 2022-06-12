<?php

namespace App\DataFixtures;

use App\Entity\ProductOption;

class ProductOptionFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'product_option_color',
            $this->createProductOption('رنگ', 'color')
        );
        $this->setReferenceAndPersist(
            'product_option_guarantee',
            $this->createProductOption('گارانتی', 'guarantee')
        );
        $this->setReferenceAndPersist(
            'product_option_shoe_size',
            $this->createProductOption('سایز کفش', 'shoe-size')
        );
        $this->setReferenceAndPersist(
            'product_option_dress_size',
            $this->createProductOption('سایز لباس', 'dress-size')
        );
        $this->setReferenceAndPersist(
            'product_option_baby_dress_size',
            $this->createProductOption('سایز لباس نوزاد', 'baby-dress-size')
        );

        $this->createMany(
            ProductOption::class,
            10,
            function (ProductOption $productOption, int $count) {
                $productOption->setName($this->faker->sentence(1));
            },
            true
        );

        $this->manager->flush();
    }

    private function createProductOption(string $name, string $code): ProductOption
    {
        return (new ProductOption())->setCode($code)->setName($name);
    }
}
