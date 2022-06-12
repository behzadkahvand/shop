<?php

namespace App\DataFixtures;

use App\Entity\Category;

class CategoryFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("category_appliance", $this->createCategory(
            'appliance',
            'appliance category product',
            $this->faker->unique()->sentence(5),
            $this->faker->words(5)
        ));

        $this->setReferenceAndPersist("category_digital", $this->createCategory(
            'digital',
            'digital category product',
            $this->faker->unique()->sentence(5),
            $this->faker->words(5)
        ));

        $this->setReferenceAndPersist("category_mobile", $this->createCategory(
            'mobile',
            'mobile category product',
            $this->faker->unique()->sentence(5),
            $this->faker->words(5),
            'category_digital',
            $this->faker->numberBetween(5, 10),
            $this->faker->numberBetween(1, 5)
        ));

        $this->setReferenceAndPersist("category_tv", $this->createCategory(
            'television',
            'television category product',
            $this->faker->unique()->sentence(5),
            $this->faker->words(5),
            'category_digital',
            $this->faker->numberBetween(5, 10),
            $this->faker->numberBetween(3, 5)
        ));

        $this->setReferenceAndPersist("category_home", $this->createCategory(
            'home',
            'home category product',
            $this->faker->unique()->sentence(5),
            $this->faker->words(5),
            'category_appliance'
        ));

        $this->setReferenceAndPersist("category_kitchen", $this->createCategory(
            'kitchen',
            'kitchen category product',
            $this->faker->unique()->sentence(5),
            $this->faker->words(5),
            'category_home',
            $this->faker->numberBetween(1, 10),
            $this->faker->numberBetween(1, 20)
        ));

        $this->setReferenceAndPersist("category_room", $this->createCategory(
            'category room',
            'room category product',
            'category_room',
            $this->faker->words(5),
            'category_home',
            $this->faker->numberBetween(5, 10),
            $this->faker->numberBetween(3, 5)
        ));

        $this->setReferenceAndPersist("category_for_category_delivery_tests", $this->createCategory(
            'category for category delivery tests',
            'delivery category product',
            $this->faker->unique()->sentence(5),
            $this->faker->words(5),
            'category_appliance',
            $this->faker->numberBetween(5, 10)
        ));

        $this->setReferenceAndPersist("category_for_category_destory_test", $this->createCategory(
            'category_without_product',
            'destroyable category',
            $this->faker->unique()->sentence(5),
            $this->faker->words(5),
            'category_appliance',
            $this->faker->numberBetween(1, 10)
        ));

        $this->setReferenceAndPersist("category_parent", $this->createCategory(
            'category parent',
            'parent_category',
            'parent_category',
            $this->faker->words(5),
        ));

        $this->setReferenceAndPersist("category_leaf", $this->createCategory(
            'child category',
            'child_category',
            'child_category',
            $this->faker->words(5),
            'category_parent',
            $this->faker->numberBetween(1, 10)
        ));

        $this->manager->flush();
    }

    private function createCategory(
        string $title,
        string $subtitle,
        string $code,
        array $configurations,
        ?string $parent = null,
        ?int $commission = null,
        ?int $maxLeadTime = null
    ): Category {
        $category = (new Category())
            ->setTitle($title)
            ->setSubtitle($subtitle)
            ->setCode($code)
            ->setConfigurations($configurations)
            ->setParent($parent ? $this->getReference($parent) : null)
            ->setCommission($commission);

        if ($maxLeadTime !== null) {
            $category->setMaxLeadTime($maxLeadTime);
        }

        return $category;
    }
}
