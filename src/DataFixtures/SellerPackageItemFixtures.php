<?php

namespace App\DataFixtures;

use App\Entity\SellerPackageItem;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SellerPackageItemFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'seller_package_item_1',
            $this->createSellerPackageItem(
                'seller_package_1',
                $this->faker->sentence(20)
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_item_2',
            $this->createSellerPackageItem(
                'seller_package_2',
                $this->faker->sentence(20)
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_item_3',
            $this->createSellerPackageItem(
                'seller_package_3',
                $this->faker->sentence(20)
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_item_4',
            $this->createSellerPackageItem(
                'seller_package_4',
                $this->faker->sentence(20)
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_item_5',
            $this->createSellerPackageItem(
                'seller_package_5',
                $this->faker->sentence(20)
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_item_6',
            $this->createSellerPackageItem(
                'seller_package_6',
                $this->faker->sentence(20)
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_item_7',
            $this->createSellerPackageItem(
                'seller_package_7',
                $this->faker->sentence(20)
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_item_8',
            $this->createSellerPackageItem(
                'seller_package_8',
                $this->faker->sentence(20)
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_item_9',
            $this->createSellerPackageItem(
                'seller_package_9',
                $this->faker->sentence(20)
            )
        );

        $this->manager->flush();
    }

    private function createSellerPackageItem(
        string $package,
        string $description
    ): SellerPackageItem {
        return (new SellerPackageItem())
            ->setPackage($this->getReference($package))
            ->setDescription($description);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            SellerPackageFixtures::class
        ];
    }
}
