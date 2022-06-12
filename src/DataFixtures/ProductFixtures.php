<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'product_1',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_mobile',
                ['product_option_color', 'product_option_guarantee', 'product_option_shoe_size'],
                'shipping_category_1',
                'product_1',
                'Company Ltd',
                $this->faker->sentence(5),
                true,
                500,
                300,
                400,
                250,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'CONFIRMED',
                null
            )
        );
        $this->setReferenceAndPersist(
            'product_2',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_mobile',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_1',
                'product_2',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                1500,
                450,
                500,
                550,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'DRAFT',
                'ADMIN'
            )
        );
        $this->setReferenceAndPersist(
            'product_3',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_mobile',
                ['product_option_color', 'product_option_guarantee', 'product_option_shoe_size'],
                'shipping_category_1',
                'product_3',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                750,
                320,
                470,
                520,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'CONFIRMED',
                'ADMIN'
            )
        );
        $this->setReferenceAndPersist(
            'product_4',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_tv',
                ['product_option_color', 'product_option_guarantee', 'product_option_baby_dress_size'],
                'shipping_category_1',
                'product_4',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                2000,
                500,
                1000,
                750,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'UNAVAILABLE',
                'ADMIN'
            )
        );
        $this->setReferenceAndPersist(
            'product_5',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_tv',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_1',
                'product_5',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                5000,
                1500,
                1500,
                1500,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'CONFIRMED',
                'ADMIN'
            )
        );
        $this->setReferenceAndPersist(
            'product_6',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_tv',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_1',
                'product_6',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                750,
                450,
                370,
                500,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'DRAFT',
                'ADMIN'
            )
        );
        $this->setReferenceAndPersist(
            'product_7',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_room',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_1',
                'product_7',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                1000,
                550,
                570,
                600,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'CONFIRMED',
                'SELLER'
            )
        );
        $this->setReferenceAndPersist(
            'product_8',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_kitchen',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_1',
                'product_8',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                2500,
                750,
                600,
                700,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'CONFIRMED',
                'ADMIN'
            )
        );
        $this->setReferenceAndPersist(
            'product_9',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_room',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_2',
                'product_9',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                600,
                300,
                420,
                500,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'CONFIRMED',
                'ADMIN',
                false
            )
        );
        $this->setReferenceAndPersist(
            'product_10',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_kitchen',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_1',
                'product_10',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                800,
                400,
                600,
                650,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'DRAFT',
                'SELLER',
                false
            )
        );
        $this->setReferenceAndPersist(
            'product_11',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_kitchen',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_1',
                'product_11',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                450,
                300,
                350,
                470,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'SOON',
                'SELLER'
            )
        );
        $this->setReferenceAndPersist(
            'product_12',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_room',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_2',
                'product_12',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                450,
                300,
                350,
                470,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'UNAVAILABLE',
                'SELLER',
                false
            )
        );
        $this->setReferenceAndPersist(
            'product_13',
            $this->createProduct(
                'brand_' . $this->faker->randomNumber(1, 10),
                'category_kitchen',
                ['product_option_color', 'product_option_guarantee'],
                'shipping_category_4',
                'product_13',
                $this->faker->company(),
                $this->faker->sentence(5),
                true,
                600,
                300,
                420,
                500,
                $this->faker->sentence(5),
                $this->faker->numberBetween(0, 1000),
                $this->faker->numberBetween(0, 200),
                'CONFIRMED',
                'ADMIN',
                false
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
            BrandFixtures::class,
            CategoryFixtures::class,
            ProductOptionFixtures::class,
            ShippingCategoryFixtures::class,
        ];
    }

    private function createProduct(
        string $brand,
        ?string $category,
        array $productOptions,
        string $shippingCategory,
        string $title,
        string $subtitle,
        string $description,
        bool $isActive,
        int $weight,
        int $height,
        int $width,
        int $length,
        string $metaDescription,
        int $visits,
        int $orderCount,
        string $status,
        ?string $channel,
        ?bool $isOriginal = null,
    ): Product {
        $product = new Product();
        $product
            ->setBrand($this->getReference($brand))
            ->setCategory($this->getReference($category))
            ->setShippingCategory($this->getReference($shippingCategory))
            ->setTitle($title)
            ->setSubtitle($subtitle)
            ->setDescription($description)
            ->setIsActive($isActive)
            ->setWeight($weight)
            ->setHeight($height)
            ->setWidth($width)
            ->setLength($length)
            ->setMetaDescription($metaDescription)
            ->setVisits($visits)
            ->setOrderCount($orderCount)
            ->setStatus($status)
            ->setChannel($channel);

        if ($isOriginal !== null) {
            $product->setIsOriginal($isOriginal);
        }

        foreach ($productOptions as $productOption) {
            $product->addOption($this->getReference($productOption));
        }

        return $product;
    }
}
