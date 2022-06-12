<?php

namespace App\DataFixtures;

use App\Entity\CategoryDiscountRange;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CategoryDiscountRangeFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $categoryDiscountRange = (new CategoryDiscountRange())
            ->setCategory($this->getReference("category_room"))
            ->setIsBounded(true)
            ->setMinDiscount(1)
            ->setMaxDiscount(20);

        $this->addReference('category_discount_1', $categoryDiscountRange);

        $this->manager->persist($categoryDiscountRange);
        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}
