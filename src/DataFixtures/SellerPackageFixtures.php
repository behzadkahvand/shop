<?php

namespace App\DataFixtures;

use App\Dictionary\SellerPackageStatus;
use App\Dictionary\SellerPackageType;
use App\Entity\SellerPackage;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SellerPackageFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->createMany(
            SellerPackage::class,
            6,
            function (SellerPackage $sellerPackage, int $i) {
                $this->createSellerPackage(
                    SellerPackageStatus::SENT,
                    SellerPackageType::NON_FMCG,
                    $this->faker->dateTime(),
                    $this->faker->sentence(20),
                    'seller_lendo',
                    $sellerPackage
                );
            },
            true
        );

        $this->setReferenceAndPersist(
            'seller_package_7',
            $this->createSellerPackage(
                SellerPackageStatus::SENT,
                SellerPackageType::FMCG,
                $this->faker->dateTime(),
                $this->faker->sentence(20),
                'seller_10',
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_8',
            $this->createSellerPackage(
                SellerPackageStatus::SENT,
                SellerPackageType::FMCG,
                $this->faker->dateTime(),
                $this->faker->sentence(20),
                'seller_10',
            )
        );

        $this->setReferenceAndPersist(
            'seller_package_9',
            $this->createSellerPackage(
                SellerPackageStatus::SENT,
                SellerPackageType::FMCG,
                $this->faker->dateTime(),
                $this->faker->sentence(20),
                'seller_10',
            )
        );

        $this->manager->flush();
    }

    private function createSellerPackage(
        string $status,
        string $type,
        \DateTime $sentAt,
        string $description,
        string $seller,
        ?SellerPackage $sellerPackage = null
    ): SellerPackage {
        if (!$sellerPackage) {
            $sellerPackage = new SellerPackage();
        }

        return $sellerPackage->setStatus($status)
                             ->setType($type)
                             ->setSentAt($sentAt)
                             ->setDescription($description)
                             ->setSeller($this->getReference($seller));
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            SellerFixtures::class
        ];
    }
}
