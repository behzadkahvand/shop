<?php

namespace App\DataFixtures;

use App\Entity\Brand;

class BrandFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->createMany(
            Brand::class,
            10,
            function (Brand $brand, int $i) {
                $this->createBrand(
                    $brand,
                    $this->faker->unique()->company(),
                    $this->faker->unique()->company(),
                    $this->faker->sentence(5),
                    $this->faker->sentence(5),
                    $this->faker->sentence(5)
                );
            },
            true
        );

        $this->setReferenceAndPersist('brand_other', $this->createBrand(
            new Brand(),
            'متفرقه',
            $this->faker->unique()->company(),
            $this->faker->sentence(5),
            $this->faker->sentence(5),
            $this->faker->sentence(5)
        ));

        $this->manager->flush();
    }

    protected function createBrand(
        Brand $brand,
        string $title,
        string $subTitle,
        string $code,
        string $description,
        string $metaDescription
    ): Brand {
        return $brand->setTitle($title)
                     ->setSubtitle($subTitle)
                     ->setCode($code)
                     ->setDescription($description)
                     ->setMetaDescription($metaDescription);
    }
}
